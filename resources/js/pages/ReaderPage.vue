<template>
    <div
        class="reader-shell bg-app text-app fixed inset-0 z-50 flex h-dvh max-h-dvh w-full flex-col overflow-hidden lg:grid lg:grid-cols-[280px_minmax(0,1fr)_300px]"
    >
        <ReaderTocPanel
            v-show="! isFullscreen"
            class="reader-shell__sidebar-left hidden lg:flex"
            :outline-items="outlineItems"
            :bookmark-items="readerBookmarks"
            :user-notes="readerNotes"
            :annotation-items="annotationItems"
            :annotations-loading="annotationsLoading"
            :current-page="livePageState.currentPage"
            :total-pages="displayTotalPages"
            :fetch-thumbnail="fetchPageThumbnail"
            :thumbnails-ready="thumbnailsReady"
            @select="onTocSelect"
            @remove-bookmark="removeReaderBookmark"
            @remove-note="removeReaderNote"
            @add-note="openNoteEditor"
            @request-annotations="loadAnnotations"
        />

        <div
            v-if="showToc && ! isFullscreen"
            class="absolute inset-0 z-20 flex lg:hidden"
        >
            <div class="flex-1 bg-black/30" @click="showToc = false" />
            <ReaderTocPanel
                class="w-[min(88vw,320px)] shadow-xl"
                :outline-items="outlineItems"
                :bookmark-items="readerBookmarks"
                :user-notes="readerNotes"
                :annotation-items="annotationItems"
                :annotations-loading="annotationsLoading"
                :current-page="livePageState.currentPage"
                :total-pages="displayTotalPages"
                :fetch-thumbnail="fetchPageThumbnail"
                :thumbnails-ready="thumbnailsReady"
                closable
                @close="showToc = false"
                @select="onTocSelect"
                @remove-bookmark="removeReaderBookmark"
                @remove-note="removeReaderNote"
                @add-note="openNoteEditor"
                @request-annotations="loadAnnotations"
            />
        </div>

        <div class="reader-shell__main flex min-h-0 min-w-0 flex-col">
            <header
                v-show="! isFullscreen"
                class="safe-top bg-surface border-app flex shrink-0 items-center justify-between border-b px-4 py-3 lg:px-6"
            >
                <button
                    type="button"
                    class="bg-surface-muted tap-feedback text-app flex items-center gap-2 rounded-full px-3 py-2 text-sm font-medium"
                    @click="router.push(`/resources/${route.params.slug}`)"
                >
                    <IconBack class="h-4 w-4" />
                    <span class="hidden sm:inline">Back</span>
                </button>

                <div class="min-w-0 flex-1 px-3 text-center lg:px-4 lg:text-left">
                    <p class="text-app truncate text-sm font-semibold">{{ resource?.title }}</p>
                    <p class="text-muted hidden truncate text-xs lg:block">{{ primaryAuthor }}</p>
                </div>

                <div class="flex items-center gap-1.5 sm:gap-2">
                    <button
                        type="button"
                        class="bg-surface-muted tap-feedback flex h-9 w-9 items-center justify-center rounded-full"
                        :class="isCurrentPageBookmarked ? 'bg-brand-subtle text-brand' : 'text-app'"
                        :title="isCurrentPageBookmarked ? 'Remove bookmark' : 'Bookmark this page'"
                        @click="toggleCurrentPageBookmark"
                    >
                        <IconBookmark class="h-4 w-4" :class="{ 'fill-current': isCurrentPageBookmarked }" />
                    </button>
                    <button
                        type="button"
                        class="bg-surface-muted tap-feedback text-app flex h-9 w-9 items-center justify-center rounded-full"
                        title="Add note"
                        @click="openNoteEditor(livePageState.currentPage)"
                    >
                        <IconNotes class="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        class="bg-surface-muted tap-feedback text-app flex h-9 w-9 items-center justify-center rounded-full lg:hidden"
                        title="Contents"
                        @click="showToc = true"
                    >
                        <IconReaderVertical class="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        class="bg-surface-muted tap-feedback text-app flex h-9 w-9 items-center justify-center rounded-full"
                        title="Full screen"
                        @click="enterFullscreen"
                    >
                        <IconReaderExpand class="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        class="bg-surface-muted tap-feedback text-app flex h-9 w-9 items-center justify-center rounded-full lg:hidden"
                        title="Reading settings"
                        @click="showSettings = true"
                    >
                        <IconEllipsisVertical class="h-4 w-4" />
                    </button>
                </div>
            </header>

            <div
                v-if="sourceError"
                class="text-red-600 flex min-h-0 flex-1 flex-col items-center justify-center gap-4 px-6 text-center text-sm"
            >
                <p>{{ sourceError }}</p>
                <button
                    class="text-brand font-medium"
                    type="button"
                    @click="loadReader"
                >
                    Try again
                </button>
            </div>

            <div
                v-else-if="sourceLoading || ! pdfSource"
                class="text-muted flex min-h-0 flex-1 items-center justify-center text-sm"
            >
                Loading document…
            </div>

            <div
                v-else
                ref="pdfStageRef"
                class="reader-shell__pdf-stage bg-app relative flex min-h-0 flex-1 flex-col"
                @click="onReaderTap"
            >
                <div
                    v-show="isFullscreen && showFullscreenChrome"
                    class="reader-fullscreen-chrome safe-top pointer-events-none absolute inset-x-0 top-0 z-30 flex items-center justify-between px-4 py-3"
                >
                    <button
                        type="button"
                        class="reader-fullscreen-chrome__button ring-app pointer-events-auto flex h-10 w-10 items-center justify-center rounded-full ring-1"
                        title="Exit full screen"
                        @click="exitFullscreen"
                    >
                        <IconReaderCompress class="h-4 w-4" />
                    </button>
                    <p class="reader-fullscreen-chrome__button ring-app pointer-events-none rounded-full px-3 py-1.5 text-xs font-medium ring-1">
                        Page {{ livePageState.currentPage }} / {{ displayTotalPages }}
                    </p>
                    <div class="pointer-events-auto flex items-center gap-2">
                        <button
                            type="button"
                            class="reader-fullscreen-chrome__button ring-app flex h-10 w-10 items-center justify-center rounded-full ring-1"
                            :class="isCurrentPageBookmarked ? 'bg-brand-subtle text-brand' : ''"
                            :title="isCurrentPageBookmarked ? 'Remove bookmark' : 'Bookmark this page'"
                            @click.stop="toggleCurrentPageBookmark"
                        >
                            <IconBookmark class="h-4 w-4" :class="{ 'fill-current': isCurrentPageBookmarked }" />
                        </button>
                        <button
                            type="button"
                            class="reader-fullscreen-chrome__button ring-app flex h-10 w-10 items-center justify-center rounded-full ring-1"
                            title="Add note"
                            @click.stop="openNoteEditor(livePageState.currentPage)"
                        >
                            <IconNotes class="h-4 w-4" />
                        </button>
                        <button
                            type="button"
                            class="reader-fullscreen-chrome__button ring-app flex h-10 w-10 items-center justify-center rounded-full ring-1"
                            :title="readingMode === 'vertical' ? 'Switch to horizontal reading' : 'Switch to vertical reading'"
                            @click.stop="toggleReadingMode"
                        >
                            <IconReaderHorizontal v-if="readingMode === 'vertical'" class="h-4 w-4" />
                            <IconReaderVertical v-else class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <button
                    v-if="isFullscreen"
                    type="button"
                    class="reader-fullscreen-exit ring-app tap-feedback flex h-11 items-center gap-2 rounded-full px-4 text-sm font-semibold ring-1"
                    @click="exitFullscreen"
                >
                    <IconReaderCompress class="h-4 w-4" />
                    Exit
                </button>

                <component
                    :is="readerViewerComponent"
                    ref="viewerRef"
                    class="min-h-0 flex-1"
                    :source="pdfSource"
                    :reading-mode="viewportReadingMode"
                    @document-ready="onDocumentReady"
                >
                    <template #footer="footerProps">
                        <ReaderPdfFooter
                            v-show="! isFullscreen"
                            v-bind="footerProps"
                            @page-state="livePageState = $event"
                        />
                    </template>
                </component>

                <ReaderNoteEditor
                    contained
                    :open="showNoteEditor"
                    :page="notePage"
                    v-model="noteDraft"
                    @close="closeNoteEditor"
                    @save="saveNote"
                />
            </div>

            <ReaderNoteEditor
                v-if="! pdfSource"
                :open="showNoteEditor"
                :page="notePage"
                v-model="noteDraft"
                @close="closeNoteEditor"
                @save="saveNote"
            />
        </div>

        <ReaderAppearancePanel
            v-show="! isFullscreen"
            class="reader-shell__sidebar-right hidden lg:flex"
            :reading-mode="readingMode"
            :horizontal-disabled="isPortrait"
            :is-fullscreen="isFullscreen"
            @update:reading-mode="readingMode = $event"
            @update:is-fullscreen="setFullscreenFromPanel"
        />

        <div v-if="showSettings && ! isFullscreen" class="absolute inset-x-0 bottom-0 z-20 lg:hidden">
            <ReaderAppearancePanel
                class="rounded-t-3xl shadow-2xl"
                :reading-mode="readingMode"
                :horizontal-disabled="isPortrait"
                :is-fullscreen="isFullscreen"
                closable
                @update:reading-mode="readingMode = $event"
                @update:is-fullscreen="setFullscreenFromPanel"
                @close="showSettings = false"
            />
        </div>
    </div>
</template>

<script setup>
import { computed, defineAsyncComponent, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { hydrateAppStorage, reloadAppStorage } from '@/lib/appStorage';
import { shouldUseMobilePdfViewer } from '@/lib/shouldUseMobilePdfViewer';
import IconBack from '@/components/icons/IconBack.vue';
import IconBookmark from '@/components/icons/IconBookmark.vue';
import IconEllipsisVertical from '@/components/icons/IconEllipsisVertical.vue';
import IconNotes from '@/components/icons/IconNotes.vue';
import IconReaderCompress from '@/components/icons/IconReaderCompress.vue';
import IconReaderExpand from '@/components/icons/IconReaderExpand.vue';
import IconReaderHorizontal from '@/components/icons/IconReaderHorizontal.vue';
import IconReaderVertical from '@/components/icons/IconReaderVertical.vue';
import ReaderAppearancePanel from '@/components/reader/ReaderAppearancePanel.vue';
import ReaderNoteEditor from '@/components/reader/ReaderNoteEditor.vue';
import ReaderPdfFooter from '@/components/reader/ReaderPdfFooter.vue';
import ReaderTocPanel from '@/components/reader/ReaderTocPanel.vue';
import { useReaderPageBookmarks } from '@/composables/useReaderPageBookmarks';
import { useReaderPreferences } from '@/composables/useReaderPreferences';
import { useReaderUserNotes } from '@/composables/useReaderUserNotes';
import { useReaderViewport } from '@/composables/useReaderViewport';
import { useToast } from '@/composables/useToast';
import { useLibrary } from '@/composables/useLibrary';
import { isPdfResource, resolvePdfDocumentSource } from '@/composables/usePdfDocumentSource';

const PdfReaderViewer = defineAsyncComponent(() => import('@/components/reader/PdfReaderViewer.vue'));
const MobilePdfViewer = defineAsyncComponent(() => import('@/components/reader/MobilePdfViewer.vue'));
const readerViewerComponent = computed(() => (
    shouldUseMobilePdfViewer() ? MobilePdfViewer : PdfReaderViewer
));

const route = useRoute();
const router = useRouter();
const readerPreferences = useReaderPreferences();
const { isPortrait, resolveReadingMode } = useReaderViewport();
const { showToast } = useToast();
const library = useLibrary();

const resource = ref(null);
const pdfSource = ref(null);
const sourceLoading = ref(true);
const sourceError = ref(null);
const viewerRef = ref(null);
const pdfStageRef = ref(null);
const showToc = ref(false);
const showSettings = ref(false);
const showNoteEditor = ref(false);
const noteDraft = ref('');
const notePage = ref(1);
const isFullscreen = ref(false);
const showFullscreenChrome = ref(true);
const readingMode = ref(readerPreferences.initialReadingMode);
const livePageState = ref({ currentPage: 1, totalPages: 1 });
const outlineItems = ref([]);
const annotationItems = ref([]);
const annotationsLoading = ref(false);
const annotationsFetched = ref(false);
const thumbnailsReady = ref(false);

const slugRef = computed(() => route.params.slug);
const {
    bookmarks: readerBookmarks,
    toggleBookmark: toggleReaderBookmark,
    isPageBookmarked,
    removeBookmark: removeReaderBookmark,
    refresh: refreshReaderBookmarks,
} = useReaderPageBookmarks(slugRef);
const {
    notes: readerNotes,
    addNote: addReaderNote,
    removeNote: removeReaderNote,
    refresh: refreshReaderNotes,
} = useReaderUserNotes(slugRef);

let fullscreenChromeTimer = null;

const primaryAuthor = computed(() => resource.value?.authors?.[0]?.name ?? '');

const metadataTotalPages = computed(() => resource.value?.metadata?.page_count ?? 1);

const displayTotalPages = computed(() => Math.max(
    metadataTotalPages.value,
    livePageState.value.totalPages || 1,
));

const isCurrentPageBookmarked = computed(() => (
    isPageBookmarked(livePageState.value.currentPage)
));

const viewportReadingMode = computed(() => resolveReadingMode(readingMode.value));

function refreshReaderLayout() {
    nextTick(() => {
        viewerRef.value?.refreshLayout();
    });
}

watch(viewportReadingMode, () => {
    refreshReaderLayout();
});

watch(isPortrait, () => {
    refreshReaderLayout();
});

watch(readingMode, () => {
    readerPreferences.persist({
        readingMode: readingMode.value,
    });
});

watch(isFullscreen, (active) => {
    if (active) {
        showFullscreenChrome.value = true;
        scheduleFullscreenChromeHide();
        return;
    }

    clearFullscreenChromeTimer();
    showFullscreenChrome.value = true;
});

function onFullscreenChange() {
    const active = document.fullscreenElement === pdfStageRef.value;
    isFullscreen.value = active;

    if (active) {
        showFullscreenChrome.value = true;
        scheduleFullscreenChromeHide();
    } else {
        clearFullscreenChromeTimer();
        showFullscreenChrome.value = true;
    }

    nextTick(() => {
        viewerRef.value?.refreshLayout();
    });
}

async function enterFullscreen() {
    const stage = pdfStageRef.value;

    if (! stage) {
        return;
    }

    try {
        if (document.fullscreenElement !== stage) {
            await stage.requestFullscreen();
        }
    } catch {
        isFullscreen.value = true;
        await nextTick();
        viewerRef.value?.refreshLayout();
    }
}

async function exitFullscreen() {
    if (document.fullscreenElement === pdfStageRef.value) {
        await document.exitFullscreen();
        return;
    }

    isFullscreen.value = false;
    await nextTick();
    viewerRef.value?.refreshLayout();
}

function setFullscreenFromPanel(enabled) {
    if (enabled) {
        enterFullscreen();
        return;
    }

    exitFullscreen();
}

function clearFullscreenChromeTimer() {
    if (fullscreenChromeTimer) {
        window.clearTimeout(fullscreenChromeTimer);
        fullscreenChromeTimer = null;
    }
}

function scheduleFullscreenChromeHide() {
    clearFullscreenChromeTimer();
    fullscreenChromeTimer = window.setTimeout(() => {
        showFullscreenChrome.value = false;
    }, 4000);
}

function onReaderTap() {
    if (! isFullscreen.value) {
        return;
    }

    showFullscreenChrome.value = ! showFullscreenChrome.value;

    if (showFullscreenChrome.value) {
        scheduleFullscreenChromeHide();
    } else {
        clearFullscreenChromeTimer();
    }
}

function toggleReadingMode() {
    readingMode.value = readingMode.value === 'vertical' ? 'horizontal' : 'vertical';
}

function onDocumentReady({ outline }) {
    outlineItems.value = outline ?? [];
    thumbnailsReady.value = true;

    if (! shouldUseMobilePdfViewer()) {
        refreshReaderLayout();
    }
}

async function loadAnnotations() {
    if (annotationsLoading.value || annotationsFetched.value) {
        return;
    }

    annotationsLoading.value = true;

    try {
        annotationItems.value = await viewerRef.value?.loadAnnotations() ?? [];
    } finally {
        annotationsLoading.value = false;
        annotationsFetched.value = true;
    }
}

function fetchPageThumbnail(pageNumber) {
    return viewerRef.value?.getPageThumbnail?.(pageNumber) ?? Promise.resolve(null);
}

function toggleCurrentPageBookmark() {
    const page = Number(livePageState.value.currentPage);

    if (! page || page < 1) {
        showToast('Wait for the page to finish loading.');
        return;
    }

    const wasBookmarked = isPageBookmarked(page);
    const saved = toggleReaderBookmark(page);

    if (wasBookmarked) {
        showToast('Bookmark removed');
        return;
    }

    if (saved) {
        showToast(`Page ${page} bookmarked`);
        return;
    }

    showToast('Could not save bookmark. Try again.');
}

function openNoteEditor(page = livePageState.value.currentPage) {
    notePage.value = Number(page) || livePageState.value.currentPage;
    noteDraft.value = '';
    showNoteEditor.value = true;
}

function closeNoteEditor() {
    showNoteEditor.value = false;
    noteDraft.value = '';
}

function saveNote() {
    const entry = addReaderNote(notePage.value, noteDraft.value);

    if (! entry) {
        return;
    }

    closeNoteEditor();
    showToast(`Note saved on page ${entry.page}`);
}

async function loadReader() {
    const slug = route.params.slug;
    sourceLoading.value = true;
    sourceError.value = null;
    pdfSource.value = null;
    outlineItems.value = [];
    annotationItems.value = [];
    annotationsFetched.value = false;
    thumbnailsReady.value = false;
    livePageState.value = { currentPage: 1, totalPages: metadataTotalPages.value || 1 };

    try {
        const response = await window.axios.get(`/resources/${slug}`);
        resource.value = response.data.data;

        if (! isPdfResource(resource.value)) {
            sourceError.value = 'In-app reading is only available for PDF resources.';
            return;
        }

        pdfSource.value = await resolvePdfDocumentSource(slug, { resource: resource.value });
        refreshReaderLayout();
    } catch (err) {
        sourceError.value = err?.message ?? 'Could not open this document.';
    } finally {
        sourceLoading.value = false;
    }
}

function onTocSelect(page) {
    viewerRef.value?.scrollToPage(page);
    showToc.value = false;
}

onMounted(() => {
    document.addEventListener('fullscreenchange', onFullscreenChange);
    document.addEventListener('visibilitychange', onReaderVisibilityChange);
    loadReader();
});

async function onReaderVisibilityChange() {
    if (document.visibilityState !== 'visible') {
        return;
    }

    await reloadAppStorage().catch(() => {});
    refreshReaderBookmarks();
    refreshReaderNotes();
    library.refreshReaderCounts();
}

onBeforeUnmount(() => {
    document.removeEventListener('fullscreenchange', onFullscreenChange);
    document.removeEventListener('visibilitychange', onReaderVisibilityChange);
    clearFullscreenChromeTimer();

    if (document.fullscreenElement === pdfStageRef.value) {
        document.exitFullscreen().catch(() => {});
    }
});

watch(() => route.params.slug, () => {
    exitFullscreen();
    loadReader();
});
</script>
