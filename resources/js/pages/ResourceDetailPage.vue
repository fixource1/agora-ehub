<template>
    <AppShell :show-bottom-nav="false">
        <ResourceDetailSkeleton v-if="showSkeleton" />

        <div
            v-else-if="error && !resource"
            class="text-muted flex min-h-[100dvh] flex-col items-center justify-center gap-4 px-6 text-center"
        >
            <p class="text-app text-sm">{{ error }}</p>
            <button
                type="button"
                class="text-brand text-sm font-medium"
                @click="loadResource(route.params.slug)"
            >
                Try again
            </button>
        </div>

        <template v-else-if="resource">
            <div class="resource-detail-page flex min-h-0 flex-1 flex-col overflow-hidden">
                <header class="safe-top bg-surface border-app shrink-0 z-20 flex items-center justify-between border-b px-4 pb-4 pt-1 lg:px-8 lg:pb-5">
                    <button
                        class="bg-surface-muted flex h-10 w-10 items-center justify-center rounded-full"
                        @click="router.push('/library')"
                    >
                        <IconBack class="h-5 w-5" />
                    </button>
                    <div class="relative flex items-center gap-2">
                        <ResourceShareMenu :resource="resource" />
                        <ResourceMoreMenu :resource="resource" />
                    </div>
                </header>

                <div class="resource-detail-scroll min-h-0 flex-1 overflow-y-auto overscroll-y-contain">
                    <div class="page-content-no-nav px-4 pt-6 pb-6 lg:px-8 lg:pt-10 lg:pb-10">
                        <div class="lg:grid lg:grid-cols-[280px_minmax(0,1fr)_320px] lg:items-start lg:gap-8 xl:gap-10">
                            <aside class="lg:sticky lg:top-6">
                        <div class="mx-auto max-w-xs lg:mx-0 lg:max-w-none">
                            <div class="resource-detail-cover relative mx-auto aspect-[3/4] w-full max-w-[148px] overflow-hidden rounded-2xl shadow-lg sm:max-w-[168px] lg:max-w-[180px]">
                                <img
                                    v-if="showCoverImage"
                                    :src="coverImageUrl"
                                    :alt="resource.title"
                                    class="h-full w-full object-cover object-center"
                                    :class="{ 'resource-detail-cover__media--downloading': downloading }"
                                    @error="coverImageFailed = true"
                                >
                                <ResourceGeneratedCover
                                    v-else
                                    :icon="typeIcon"
                                    :category="resource.category?.slug"
                                    :category-name="resource.category?.name"
                                    :title="resource.title"
                                    :type-slug="resource.resource_type?.slug"
                                    :file-type="resource.primary_file?.file_type"
                                    show-title
                                    :class="{ 'resource-detail-cover__media--downloading': downloading }"
                                />

                                <Transition name="cover-badge">
                                    <div
                                        v-if="coverDownloadState !== 'idle'"
                                        class="resource-detail-cover__download-badge absolute right-2 top-2 z-20 sm:right-2.5 sm:top-2.5"
                                    >
                                        <CircularDownloadControl
                                            :state="coverDownloadState"
                                            :progress="downloadProgress"
                                            size="md"
                                            variant="cover"
                                            :aria-label="coverDownloadAriaLabel"
                                            @click="handleCoverDownloadClick"
                                        />
                                    </div>
                                </Transition>
                            </div>

                            <p class="text-muted mt-4 text-center text-sm lg:text-left">{{ fileMeta }}</p>

                            <button
                                class="bg-brand mt-4 flex w-full items-center justify-center gap-2.5 rounded-2xl px-4 py-3.5 text-base font-semibold text-white shadow-lg transition disabled:cursor-not-allowed disabled:opacity-90"
                                type="button"
                                @click="handleDownloadButtonClick"
                            >
                                <CircularDownloadControl
                                    v-if="downloading"
                                    state="downloading"
                                    :progress="downloadProgress"
                                    size="sm"
                                    variant="button"
                                    aria-label="Cancel download"
                                    class="pointer-events-none shrink-0"
                                />
                                <IconCheck v-else-if="isOffline" class="h-5 w-5 shrink-0" />
                                <IconDownload v-else class="h-5 w-5 shrink-0" />
                                {{ downloadButtonLabel }}
                            </button>

                            <p
                                v-if="downloadError"
                                class="text-red-600 mt-3 text-center text-sm"
                            >
                                {{ downloadError }}
                            </p>

                            <button
                                v-if="isPdf"
                                class="bg-surface ring-app text-app mt-3 flex w-full items-center justify-center gap-2 rounded-2xl px-4 py-3.5 font-medium ring-1 disabled:cursor-not-allowed disabled:opacity-60"
                                type="button"
                                :disabled="! canReadPdf"
                                @click="openReader"
                            >
                                Read
                            </button>

                            <div v-if="progress" class="bg-surface ring-app mt-4 rounded-2xl p-4 ring-1">
                                <div class="mb-2 flex items-center justify-between text-sm">
                                    <span class="font-medium">{{ progress.percentage }}% complete</span>
                                    <IconBookmark class="text-brand h-4 w-4" />
                                </div>
                                <div class="bg-surface-muted h-2 overflow-hidden rounded-full">
                                    <div class="bg-brand h-full rounded-full" :style="{ width: `${progress.percentage}%` }" />
                                </div>
                                <p class="text-muted mt-2 text-xs">{{ progress.current }} / {{ progress.total }} pages</p>
                            </div>
                        </div>
                    </aside>

                    <div class="mt-8 space-y-6 lg:mt-0">
                        <div>
                            <h1 class="text-app text-2xl font-bold leading-tight lg:text-3xl">{{ resource.title }}</h1>
                            <p v-if="primaryAuthor" class="text-brand mt-1 text-base font-medium lg:text-lg">{{ primaryAuthor }}</p>
                            <div v-if="resource.category" class="mt-3">
                                <span class="bg-surface-muted text-app rounded-full px-3 py-1 text-xs font-medium">
                                    {{ resource.category.name }}
                                </span>
                            </div>
                        </div>

                        <div class="bg-surface ring-app rounded-2xl p-4 ring-1 lg:p-6">
                            <p class="text-muted text-sm leading-7 lg:text-base" :class="{ 'line-clamp-4': !showFullDescription }">
                                {{ resource.description || 'No description available.' }}
                            </p>
                            <button
                                v-if="(resource.description?.length ?? 0) > 160"
                                class="text-brand mt-2 text-sm font-medium"
                                @click="showFullDescription = !showFullDescription"
                            >
                                {{ showFullDescription ? 'Less' : 'More' }}
                            </button>
                        </div>

                        <div class="bg-surface ring-app overflow-hidden rounded-2xl ring-1">
                            <button
                                v-for="row in contentRows"
                                :key="row.label"
                                class="border-app flex w-full items-center justify-between border-b px-4 py-4 text-left last:border-0 lg:px-6"
                            >
                                <span class="text-sm font-medium">{{ row.label }}</span>
                                <span class="text-muted flex items-center gap-2 text-sm">
                                    <span v-if="row.count">{{ row.count }}</span>
                                    ›
                                </span>
                            </button>
                        </div>

                        <div
                            id="resource-information"
                            class="bg-surface ring-app scroll-mt-28 overflow-hidden rounded-2xl ring-1 lg:scroll-mt-32"
                        >
                            <h2 class="border-app border-b px-4 py-3 text-sm font-semibold lg:px-6 lg:py-4">Information</h2>
                            <dl class="grid gap-0 lg:grid-cols-2">
                                <div
                                    v-for="row in infoRows"
                                    :key="row.label"
                                    class="border-app grid grid-cols-[110px_1fr] gap-3 border-b px-4 py-3 text-sm last:border-0 lg:grid-cols-[120px_1fr] lg:px-6 lg:py-4"
                                >
                                    <dt class="text-muted">{{ row.label }}</dt>
                                    <dd class="text-app font-medium">{{ row.value }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                            <aside class="mt-8 space-y-4 lg:sticky lg:top-6 lg:mt-0">
                        <div class="bg-surface ring-app overflow-hidden rounded-2xl ring-1">
                            <h2 class="border-app border-b px-4 py-3 text-sm font-semibold lg:px-5">You might also like</h2>
                            <div v-if="relatedLoading" class="px-4 py-6 lg:px-5">
                                <p class="text-muted text-sm">Loading suggestions…</p>
                            </div>
                            <div v-else-if="relatedResources.length" class="divide-app divide-y">
                                <ResourceRelatedItem
                                    v-for="related in relatedResources"
                                    :key="related.slug"
                                    :resource="related"
                                />
                            </div>
                            <p v-else class="text-muted px-4 py-6 text-sm lg:px-5">No related resources yet.</p>
                        </div>

                        <div v-if="resource.files?.length" class="bg-surface ring-app overflow-hidden rounded-2xl ring-1">
                            <h2 class="border-app border-b px-4 py-3 text-sm font-semibold lg:px-5">Files</h2>
                            <div
                                v-for="file in resource.files"
                                :key="file.id"
                                class="flex items-center justify-between gap-3 px-4 py-3 lg:px-5"
                            >
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium">{{ file.file_name }}</p>
                                    <p class="text-muted text-xs">{{ formatSize(file.file_size) }}</p>
                                </div>
                                <span
                                    v-if="isOffline && file.is_primary"
                                    class="text-brand shrink-0"
                                    title="Available offline"
                                >
                                    <IconCheck class="h-4 w-4" />
                                </span>
                            </div>
                        </div>
                    </aside>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AppShell>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, toRef, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppShell from '@/layouts/AppShell.vue';
import ResourceDetailSkeleton from '@/components/resources/ResourceDetailSkeleton.vue';
import ResourceGeneratedCover from '@/components/resources/ResourceGeneratedCover.vue';
import ResourceRelatedItem from '@/components/resources/ResourceRelatedItem.vue';
import ResourceShareMenu from '@/components/resources/ResourceShareMenu.vue';
import ResourceMoreMenu from '@/components/resources/ResourceMoreMenu.vue';
import CircularDownloadControl from '@/components/resources/CircularDownloadControl.vue';
import IconBack from '@/components/icons/IconBack.vue';
import IconBookmark from '@/components/icons/IconBookmark.vue';
import IconCheck from '@/components/icons/IconCheck.vue';
import IconDownload from '@/components/icons/IconDownload.vue';
import { useLibrary } from '@/composables/useLibrary';
import { useOfflineDownload } from '@/composables/useOfflineDownload';
import { canReadPdfResource, isPdfResource } from '@/composables/usePdfDocumentSource';
import { getOfflineResource } from '@/composables/useOfflineStore';
import { useResourceCache } from '@/composables/useResourceCache';
import { useDelayedLoading } from '@/composables/useDelayedLoading';
import { useResourceMeta } from '@/composables/useResourceMeta';
import { useResourceCover } from '@/composables/useResourceCover';

const route = useRoute();
const router = useRouter();
const library = useLibrary();
const { downloadResource, removeDownload } = useOfflineDownload();
const { getResource, fetchResource, hasDetailData, setResource, seedFromList } = useResourceCache();

function resolveInitialResource(slug = route.params.slug) {
    const fromState = history.state?.resource;
    const fromCache = getResource(slug);

    if (fromState?.slug === slug && fromCache) {
        return hasDetailData(fromCache) ? fromCache : fromState;
    }

    if (fromState?.slug === slug) {
        return fromState;
    }

    return fromCache;
}

const resource = ref(resolveInitialResource());
const resourceRef = computed(() => resource.value);
const { coverImageUrl, showCoverImage, coverImageFailed } = useResourceCover(resourceRef);
const loading = ref(! resource.value);
const error = ref(null);
const showFullDescription = ref(false);
const downloading = ref(false);
const downloadProgress = ref(0);
const downloadError = ref('');
let progressInterval = null;
let downloadAbortController = null;
const { typeIcon } = useResourceMeta(resource);

const isPdf = computed(() => isPdfResource(resource.value));
const canReadPdf = computed(() => canReadPdfResource(resource.value, { offline: isOffline.value }));

const needsSkeleton = computed(() => loading.value && ! resource.value);
const { showSkeleton } = useDelayedLoading(needsSkeleton);

async function loadResource(slug) {
    error.value = null;
    const cached = resolveInitialResource(slug);
    const offline = await getOfflineResource(slug);

    if (offline) {
        resource.value = offline;
        setResource(slug, offline);
        library.markDownloaded(slug);
        loading.value = false;

        if (! hasDetailData(offline)) {
            try {
                resource.value = await fetchResource(slug);
            } catch {
                // Keep offline copy when the network is unavailable.
            }
        }

        await loadRelatedResources(resource.value);

        return;
    }

    if (cached) {
        resource.value = cached;
        loading.value = false;

        if (! hasDetailData(cached)) {
            try {
                resource.value = await fetchResource(slug);
            } catch {
                if (! resource.value) {
                    error.value = 'Could not load this resource. Check your connection and try again.';
                }
            }
        }

        await loadRelatedResources(resource.value);

        return;
    }

    loading.value = true;

    try {
        resource.value = await fetchResource(slug);
        await loadRelatedResources(resource.value);
    } catch {
        error.value = 'Could not load this resource. Check your connection and try again.';
    } finally {
        loading.value = false;
    }
}

const isOffline = computed(() => resource.value && library.isDownloaded(resource.value.slug));
const primaryAuthor = computed(() => resource.value?.authors?.[0]?.name ?? null);

const downloadButtonLabel = computed(() => {
    if (downloading.value) {
        return 'Downloading…';
    }

    return isOffline.value ? 'Remove from Device' : 'Download for Offline';
});

const coverDownloadState = computed(() => {
    if (downloading.value) {
        return 'downloading';
    }

    if (isOffline.value) {
        return 'completed';
    }

    return 'idle';
});

const coverDownloadAriaLabel = computed(() => {
    if (downloading.value) {
        return 'Cancel download';
    }

    if (isOffline.value) {
        return 'Available offline';
    }

    return 'Download';
});

const fileMeta = computed(() => {
    const file = resource.value?.primary_file;
    const meta = resource.value?.metadata;
    const parts = [];
    if (file?.file_type) parts.push(file.file_type.toUpperCase());
    if (file?.file_size) parts.push(formatSize(file.file_size));
    if (meta?.page_count) parts.push(`${meta.page_count} pages`);
    return parts.join(' • ') || 'Digital resource';
});

const progress = computed(() => {
    const pages = resource.value?.metadata?.page_count;
    if (!pages) return null;
    return { percentage: 35, current: Math.round(pages * 0.35), total: pages };
});

const contentRows = [
    { label: 'Table of Contents', count: null },
    { label: 'Bookmarks', count: 12 },
    { label: 'Highlights', count: 8 },
    { label: 'Notes', count: 5 },
];

const infoRows = computed(() => [
    { label: 'Publisher', value: resource.value?.metadata?.publisher ?? '—' },
    { label: 'Published', value: resource.value?.metadata?.publication_date ?? '—' },
    { label: 'Language', value: resource.value?.language ?? '—' },
    { label: 'ISBN', value: resource.value?.metadata?.isbn ?? '—' },
]);

const relatedResources = ref([]);
const relatedLoading = ref(false);

async function loadRelatedResources(current) {
    if (! current?.slug) {
        relatedResources.value = [];

        return;
    }

    relatedLoading.value = true;

    try {
        const params = { per_page: 8 };

        if (current.category?.slug) {
            params.category = current.category.slug;
        }

        const { data } = await window.axios.get('/resources', { params });

        relatedResources.value = (data.data ?? [])
            .filter((item) => item.slug !== current.slug)
            .slice(0, 4);

        seedFromList(relatedResources.value);
    } catch {
        relatedResources.value = [];
    } finally {
        relatedLoading.value = false;
    }
}

function formatSize(bytes) {
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function clearDownloadTimers() {
    if (progressInterval) {
        window.clearInterval(progressInterval);
        progressInterval = null;
    }
}

function cancelDownload() {
    downloadAbortController?.abort();
    clearDownloadTimers();
    downloading.value = false;
    downloadProgress.value = 0;
    downloadAbortController = null;
}

async function removeOffline() {
    if (! resource.value) {
        return;
    }

    try {
        await removeDownload(resource.value.slug);
        downloadProgress.value = 0;
    } catch {
        error.value = 'Could not remove this download. Try again.';
    }
}

async function startDownload() {
    if (! resource.value || downloading.value) {
        return;
    }

    downloading.value = true;
    downloadProgress.value = 0;
    downloadError.value = '';
    downloadAbortController = new AbortController();

    try {
        const saved = await downloadResource(resource.value, {
            signal: downloadAbortController.signal,
            onProgress: (progress) => {
                downloadProgress.value = progress;
            },
        });

        resource.value = saved;
        await new Promise((resolve) => window.setTimeout(resolve, 250));
    } catch (err) {
        const wasCancelled = err?.code === 'ERR_CANCELED' || err?.name === 'CanceledError' || err?.name === 'AbortError';

        if (! wasCancelled) {
            downloadProgress.value = 0;
            downloadError.value = err?.message || 'Download failed. Try again.';
        }
    } finally {
        clearDownloadTimers();
        downloading.value = false;
        downloadAbortController = null;

        if (! library.isDownloaded(resource.value?.slug)) {
            downloadProgress.value = 0;
        }
    }
}

function openReader() {
    if (! canReadPdf.value || ! resource.value?.slug) {
        return;
    }

    router.push({ name: 'resource.read', params: { slug: resource.value.slug } });
}

function handleDownloadButtonClick() {
    if (downloading.value) {
        cancelDownload();

        return;
    }

    if (isOffline.value) {
        removeOffline();

        return;
    }

    startDownload();
}

function handleCoverDownloadClick() {
    if (downloading.value) {
        cancelDownload();
    }
}

onMounted(() => loadResource(route.params.slug));

onBeforeUnmount(() => {
    cancelDownload();
});

watch(() => route.params.slug, (slug) => {
    if (! slug) {
        return;
    }

    cancelDownload();
    showFullDescription.value = false;
    error.value = null;
    resource.value = resolveInitialResource(slug);
    loading.value = ! resource.value;
    loadResource(slug);
});
</script>
