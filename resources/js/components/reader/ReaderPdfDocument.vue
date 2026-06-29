<template>
    <DocumentContent :document-id="documentId" v-slot="{ isLoaded, isLoading, isError, documentState: docState }">
        <div v-if="isError" class="text-red-600 flex min-h-0 flex-1 flex-col items-center justify-center gap-3 px-6 text-center text-sm">
            <p>{{ docState?.errorMessage ?? 'Could not open this PDF.' }}</p>
        </div>

        <div
            v-else-if="isLoading"
            class="text-muted flex min-h-0 flex-1 items-center justify-center text-sm"
        >
            Opening document…
        </div>

        <div v-else-if="isLoaded" class="relative flex h-full min-h-0 flex-1 flex-col">
            <div class="relative flex h-full min-h-0 flex-1 flex-col">
                <Viewport
                    :document-id="documentId"
                    class="pdf-reader-viewport bg-surface-muted h-full min-h-0 w-full"
                >
                    <ZoomGestureWrapper :document-id="documentId">
                        <Scroller :key="readingMode" :document-id="documentId" v-slot="{ page }">
                            <div class="h-full w-full">
                                <RenderLayer :document-id="documentId" :page-index="page.pageIndex" />
                            </div>
                        </Scroller>
                    </ZoomGestureWrapper>
                </Viewport>

                <ReaderFloatingZoomControls
                    :visible="showFloatingZoom"
                    :zoom-state="zoomState"
                    :can-zoom-in="canZoomInNow"
                    :can-zoom-out="canZoomOutNow"
                    @zoom-in="zoomIn"
                    @zoom-out="zoomOut"
                    @reset="refreshViewportZoom"
                />
            </div>

            <slot
                name="footer"
                :scroll-to-page="scrollToPage"
                :page-state="state"
            />
        </div>
    </DocumentContent>
</template>

<script setup>
import { useDocumentState } from '@embedpdf/core/vue';
import { DocumentContent } from '@embedpdf/plugin-document-manager/vue';
import { RenderLayer } from '@embedpdf/plugin-render/vue';
import { ScrollStrategy } from '@embedpdf/plugin-scroll';
import { Scroller, useScroll, useScrollCapability } from '@embedpdf/plugin-scroll/vue';
import { Viewport, useViewportScrollActivity } from '@embedpdf/plugin-viewport/vue';
import { ZoomMode } from '@embedpdf/plugin-zoom';
import { useZoom, ZoomGestureWrapper } from '@embedpdf/plugin-zoom/vue';
import { computed, inject, onBeforeUnmount, ref, watch } from 'vue';
import { flattenPdfAnnotations } from '@/lib/pdfAnnotations';
import { flattenPdfOutline } from '@/lib/pdfOutline';
import { canZoomIn, canZoomOut } from '@/lib/readerZoom';
import ReaderFloatingZoomControls from '@/components/reader/ReaderFloatingZoomControls.vue';

const props = defineProps({
    documentId: {
        type: String,
        required: true,
    },
    readingMode: {
        type: String,
        default: 'vertical',
    },
});

const emit = defineEmits(['document-ready', 'zoom-change']);

const engine = inject('pdf-reader-engine', null);

const documentState = useDocumentState(() => props.documentId);
const { state, provides } = useScroll(() => props.documentId);
const { provides: scrollProvides } = useScrollCapability();
const { state: zoomState, provides: zoomProvides } = useZoom(() => props.documentId);
const scrollActivity = useViewportScrollActivity(() => props.documentId);

const outlineLoaded = ref(false);
const annotationsLoaded = ref(false);
let annotationsPromise = null;
const thumbnailUrls = new Map();

const canZoomInNow = computed(() => canZoomIn(zoomState.value?.currentZoomLevel));
const canZoomOutNow = computed(() => canZoomOut(zoomState.value?.currentZoomLevel));
const zoomUiPinned = ref(false);
let zoomUiPinTimer = null;

const showFloatingZoom = computed(() => (
    zoomUiPinned.value
    || (! scrollActivity.value?.isScrolling && ! scrollActivity.value?.isSmoothScrolling)
));

function pinZoomUi() {
    zoomUiPinned.value = true;

    if (zoomUiPinTimer) {
        window.clearTimeout(zoomUiPinTimer);
    }

    zoomUiPinTimer = window.setTimeout(() => {
        zoomUiPinned.value = false;
        zoomUiPinTimer = null;
    }, 2500);
}

function getZoomScope() {
    return zoomProvides.value ?? null;
}

function zoomIn() {
    pinZoomUi();
    getZoomScope()?.zoomIn();
}

function zoomOut() {
    pinZoomUi();
    getZoomScope()?.zoomOut();
}

function getLoadedDocument() {
    const pdfEngine = engine?.value;
    const docState = documentState.value;

    if (! pdfEngine || docState?.status !== 'loaded' || ! docState.document) {
        return null;
    }

    return { pdfEngine, doc: docState.document };
}

async function loadOutline() {
    const loaded = getLoadedDocument();

    if (! loaded) {
        return [];
    }

    try {
        const result = await loaded.pdfEngine.getBookmarks(loaded.doc).toPromise();

        return flattenPdfOutline(result.bookmarks ?? []);
    } catch {
        return [];
    }
}

async function loadAnnotations() {
    if (annotationsLoaded.value) {
        return annotationsPromise ?? [];
    }

    const loaded = getLoadedDocument();

    if (! loaded) {
        return [];
    }

    annotationsPromise = loaded.pdfEngine
        .getAllAnnotations(loaded.doc)
        .toPromise()
        .then((result) => flattenPdfAnnotations(result ?? {}))
        .catch(() => [])
        .finally(() => {
            annotationsLoaded.value = true;
        });

    return annotationsPromise;
}

watch(
    () => zoomState.value?.currentZoomLevel,
    (level) => {
        if (level != null) {
            emit('zoom-change', level);
        }
    },
    { immediate: true },
);

watch(
    () => documentState.value?.status,
    async (status) => {
        if (status !== 'loaded' || outlineLoaded.value) {
            return;
        }

        outlineLoaded.value = true;
        const outline = await loadOutline();
        emit('document-ready', { outline });
    },
);

function clearThumbnailCache() {
    thumbnailUrls.forEach((url) => URL.revokeObjectURL(url));
    thumbnailUrls.clear();
}

async function renderPageThumbnail(pageNumber) {
    const page = Number(pageNumber);

    if (! Number.isFinite(page) || page < 1) {
        return null;
    }

    if (thumbnailUrls.has(page)) {
        return thumbnailUrls.get(page);
    }

    const loaded = getLoadedDocument();

    if (! loaded) {
        return null;
    }

    const pageObject = loaded.doc.pages[page - 1];

    if (! pageObject) {
        return null;
    }

    try {
        const blob = await loaded.pdfEngine.renderThumbnail(loaded.doc, pageObject, {
            scaleFactor: 0.22,
            imageType: 'image/jpeg',
            imageQuality: 0.72,
        }).toPromise();

        if (! (blob instanceof Blob)) {
            return null;
        }

        const url = URL.createObjectURL(blob);
        thumbnailUrls.set(page, url);

        return url;
    } catch {
        return null;
    }
}

watch(
    () => props.documentId,
    () => {
        outlineLoaded.value = false;
        annotationsLoaded.value = false;
        annotationsPromise = null;
        clearThumbnailCache();
    },
);

onBeforeUnmount(() => {
    clearThumbnailCache();

    if (zoomUiPinTimer) {
        window.clearTimeout(zoomUiPinTimer);
    }
});

function applyReadingMode() {
    const docId = props.documentId;
    const scroll = scrollProvides.value;

    if (! scroll || ! docId || documentState.value?.status !== 'loaded') {
        return;
    }

    const strategy = props.readingMode === 'horizontal'
        ? ScrollStrategy.Horizontal
        : ScrollStrategy.Vertical;

    scroll.forDocument(docId).setScrollStrategy(strategy);

    refreshViewportZoom();
}

function refreshViewportZoom() {
    pinZoomUi();

    const zoomScope = zoomProvides.value;

    if (! zoomScope || documentState.value?.status !== 'loaded') {
        return;
    }

    zoomScope.requestZoom(
        props.readingMode === 'horizontal' ? ZoomMode.FitPage : ZoomMode.FitWidth,
    );
}

watch(
    [
        scrollProvides,
        zoomProvides,
        () => props.documentId,
        () => props.readingMode,
        () => documentState.value?.status,
    ],
    applyReadingMode,
);

function scrollToPage(pageNumber) {
    provides.value?.scrollToPage({
        pageNumber,
        behavior: 'instant',
    });
}

function scrollToPreviousPage() {
    provides.value?.scrollToPreviousPage('instant');
}

function scrollToNextPage() {
    provides.value?.scrollToNextPage('instant');
}

defineExpose({
    scrollToPage,
    scrollToPreviousPage,
    scrollToNextPage,
    loadOutline,
    loadAnnotations,
    renderPageThumbnail,
    refreshViewportZoom,
    zoomIn,
    zoomOut,
    zoomState,
    state,
});
</script>
