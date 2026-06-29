<template>
    <div class="mobile-pdf-viewer flex min-h-0 flex-1 flex-col">
        <div
            v-if="loading"
            class="text-muted flex min-h-0 flex-1 items-center justify-center text-sm"
        >
            Loading document…
        </div>

        <div
            v-else-if="error"
            class="text-red-600 flex min-h-0 flex-1 items-center justify-center px-6 text-center text-sm"
        >
            {{ error }}
        </div>

        <template v-else>
            <div
                ref="scrollerRef"
                class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-3 py-4"
            >
                <div
                    v-for="pageNumber in totalPages"
                    :key="pageNumber"
                    :ref="(element) => setPageElement(pageNumber, element)"
                    class="mx-auto mb-4 flex max-w-3xl justify-center last:mb-0"
                >
                    <canvas
                        :ref="(element) => setCanvasElement(pageNumber, element)"
                        class="bg-white shadow-sm"
                    />
                </div>
            </div>

            <slot
                name="footer"
                :scroll-to-page="scrollToPage"
                :page-state="pageState"
            />
        </template>
    </div>
</template>

<script setup>
import {
    nextTick,
    onBeforeUnmount,
    onMounted,
    reactive,
    ref,
    watch,
} from 'vue';
import { loadPdfArrayBuffer } from '@/lib/pdfDocumentData';
import { configurePdfJsWorker } from '@/lib/pdfjsWorker';

const props = defineProps({
    source: {
        type: Object,
        required: true,
    },
    readingMode: {
        type: String,
        default: 'vertical',
    },
});

const emit = defineEmits(['document-ready', 'zoom-change']);

const loading = ref(true);
const error = ref('');
const totalPages = ref(0);
const pageState = reactive({ currentPage: 1, totalPages: 1 });
const scrollerRef = ref(null);
const pageElements = new Map();
const canvasElements = new Map();

let pdfDocument = null;
let pdfjsLib = null;
let pageObserver = null;
let renderQueue = Promise.resolve();
let loadGeneration = 0;

function setPageElement(pageNumber, element) {
    if (element) {
        pageElements.set(pageNumber, element);
        return;
    }

    pageElements.delete(pageNumber);
}

function setCanvasElement(pageNumber, element) {
    if (element) {
        canvasElements.set(pageNumber, element);
        return;
    }

    canvasElements.delete(pageNumber);
}

function runExclusive(task) {
    const run = renderQueue.then(() => task());
    renderQueue = run.catch(() => {});

    return run;
}

function cancelActiveRenderTasks() {
    renderQueue = Promise.resolve();
}

async function buildOutlineItems(outline) {
    if (! outline?.length || ! pdfDocument) {
        return [];
    }

    const items = [];

    async function walk(entries, depth = 0) {
        for (const [index, entry] of entries.entries()) {
            let page = null;

            try {
                if (entry.dest) {
                    const destination = typeof entry.dest === 'string'
                        ? await pdfDocument.getDestination(entry.dest)
                        : entry.dest;

                    if (Array.isArray(destination) && destination[0]) {
                        const pageIndex = await pdfDocument.getPageIndex(destination[0]);
                        page = pageIndex + 1;
                    }
                }
            } catch {
                page = null;
            }

            items.push({
                id: `${depth}-${index}-${entry.title}`,
                title: entry.title?.trim() || 'Untitled',
                page,
                depth,
            });

            if (entry.items?.length) {
                await walk(entry.items, depth + 1);
            }
        }
    }

    await walk(outline);

    return items;
}

function computeScale(page, canvas) {
    const containerWidth = scrollerRef.value?.clientWidth ?? canvas.clientWidth ?? 360;
    const maxWidth = Math.max(240, containerWidth - 24);
    const viewport = page.getViewport({ scale: 1 });

    return maxWidth / viewport.width;
}

async function renderPage(pageNumber, generation) {
    if (! pdfDocument || generation !== loadGeneration) {
        return;
    }

    const canvas = canvasElements.get(pageNumber);

    if (! canvas) {
        return;
    }

    const page = await pdfDocument.getPage(pageNumber);

    if (generation !== loadGeneration) {
        return;
    }

    const scale = computeScale(page, canvas);
    const viewport = page.getViewport({ scale });
    const context = canvas.getContext('2d');

    if (! context) {
        return;
    }

    canvas.width = Math.floor(viewport.width);
    canvas.height = Math.floor(viewport.height);

    const renderTask = page.render({
        canvasContext: context,
        viewport,
    });

    try {
        await renderTask.promise;
    } catch (renderError) {
        if (renderError?.name !== 'RenderingCancelledException') {
            throw renderError;
        }
    }
}

async function renderAllPages(generation) {
    for (let pageNumber = 1; pageNumber <= totalPages.value; pageNumber++) {
        await renderPage(pageNumber, generation);
    }
}

function updateCurrentPageFromScroll() {
    const scroller = scrollerRef.value;

    if (! scroller || ! totalPages.value) {
        return;
    }

    const marker = scroller.getBoundingClientRect().top + (scroller.clientHeight * 0.35);
    let closestPage = 1;
    let closestDistance = Number.POSITIVE_INFINITY;

    for (let pageNumber = 1; pageNumber <= totalPages.value; pageNumber++) {
        const element = pageElements.get(pageNumber);

        if (! element) {
            continue;
        }

        const distance = Math.abs(element.getBoundingClientRect().top - marker);

        if (distance < closestDistance) {
            closestDistance = distance;
            closestPage = pageNumber;
        }
    }

    pageState.currentPage = closestPage;
}

function setupPageObserver() {
    pageObserver?.disconnect();

    pageObserver = new IntersectionObserver(() => {
        updateCurrentPageFromScroll();
    }, {
        root: scrollerRef.value,
        rootMargin: '0px',
        threshold: [0, 0.25, 0.5, 0.75, 1],
    });

    for (const [pageNumber, element] of pageElements.entries()) {
        element.dataset.pageNumber = String(pageNumber);
        pageObserver.observe(element);
    }

    scrollerRef.value?.addEventListener('scroll', onScrollerScroll, { passive: true });
}

function onScrollerScroll() {
    updateCurrentPageFromScroll();
}

async function loadDocument() {
    const generation = ++loadGeneration;

    loading.value = true;
    error.value = '';
    cancelActiveRenderTasks();
    pdfDocument = null;

    try {
        pdfjsLib = await import('pdfjs-dist/legacy/build/pdf.mjs');
        configurePdfJsWorker(pdfjsLib);

        const data = await loadPdfArrayBuffer(props.source);
        const task = pdfjsLib.getDocument({
            data,
            disableFontFace: true,
            useSystemFonts: true,
        });

        pdfDocument = await task.promise;

        if (generation !== loadGeneration) {
            return;
        }

        totalPages.value = pdfDocument.numPages;
        pageState.totalPages = pdfDocument.numPages;
        pageState.currentPage = 1;

        const outline = await buildOutlineItems(await pdfDocument.getOutline());

        loading.value = false;
        await nextTick();

        if (generation !== loadGeneration) {
            return;
        }

        setupPageObserver();
        await runExclusive(() => renderAllPages(generation));
        updateCurrentPageFromScroll();

        if (generation !== loadGeneration) {
            return;
        }

        emit('document-ready', { outline });
    } catch (loadError) {
        if (generation !== loadGeneration) {
            return;
        }

        error.value = loadError instanceof Error
            ? loadError.message
            : 'Could not open this document.';
        loading.value = false;
    }
}

function scrollToPage(pageNumber) {
    const target = pageElements.get(Number(pageNumber));

    if (! target) {
        return;
    }

    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    pageState.currentPage = Number(pageNumber);
}

async function renderPageThumbnail(pageNumber) {
    if (! pdfDocument) {
        return null;
    }

    return runExclusive(async () => {
        const page = await pdfDocument.getPage(pageNumber);
        const viewport = page.getViewport({ scale: 0.22 });
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');

        if (! context) {
            return null;
        }

        canvas.width = Math.floor(viewport.width);
        canvas.height = Math.floor(viewport.height);

        await page.render({
            canvasContext: context,
            viewport,
        }).promise;

        return canvas.toDataURL('image/jpeg', 0.82);
    });
}

function refreshLayout() {
    const generation = loadGeneration;

    void runExclusive(async () => {
        await renderAllPages(generation);
        updateCurrentPageFromScroll();
    });
}

watch(() => props.source, () => {
    loadDocument();
}, { deep: true });

onMounted(loadDocument);

onBeforeUnmount(() => {
    loadGeneration += 1;
    pageObserver?.disconnect();
    scrollerRef.value?.removeEventListener('scroll', onScrollerScroll);
    cancelActiveRenderTasks();
    pdfDocument?.destroy?.();
    pdfDocument = null;
});

defineExpose({
    scrollToPage,
    loadAnnotations: async () => [],
    getPageThumbnail: renderPageThumbnail,
    refreshLayout,
    zoomIn: () => {},
    zoomOut: () => {},
});
</script>
