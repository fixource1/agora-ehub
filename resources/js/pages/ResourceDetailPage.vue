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
            <header class="safe-top bg-surface border-app sticky top-0 z-20 flex items-center justify-between border-b px-4 pb-4 pt-1 lg:px-8 lg:pb-5">
                <button
                    class="bg-surface-muted flex h-10 w-10 items-center justify-center rounded-full"
                    @click="router.push('/library')"
                >
                    <IconBack class="h-5 w-5" />
                </button>
                <div class="flex items-center gap-2">
                    <span class="hidden items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 sm:flex dark:bg-emerald-950/40 dark:text-emerald-300">
                        <IconCloudOffline class="h-4 w-4" />
                        Available offline
                    </span>
                    <button class="bg-surface-muted text-muted flex h-10 w-10 items-center justify-center rounded-full">↗</button>
                    <button class="bg-surface-muted text-muted flex h-10 w-10 items-center justify-center rounded-full">⋯</button>
                </div>
            </header>

            <div class="page-content-no-nav px-4 pt-6 pb-6 lg:px-8 lg:pt-10 lg:pb-10">
                <div class="lg:grid lg:grid-cols-[280px_minmax(0,1fr)_320px] lg:items-start lg:gap-8 xl:gap-10">
                    <aside class="lg:sticky lg:top-24">
                        <div class="mx-auto max-w-xs lg:mx-0 lg:max-w-none">
                            <div class="bg-surface-muted relative mx-auto aspect-square max-w-[220px] overflow-hidden rounded-2xl shadow-lg lg:max-w-none">
                                <img
                                    v-if="resource.cover_image"
                                    :src="resource.cover_image"
                                    :alt="resource.title"
                                    class="h-full w-full object-cover"
                                >
                                <span
                                    v-if="isOffline"
                                    class="absolute right-2 top-2 flex h-7 w-7 items-center justify-center rounded-full bg-emerald-500 text-sm text-white"
                                >✓</span>
                            </div>

                            <p class="text-muted mt-4 text-center text-sm lg:text-left">{{ fileMeta }}</p>

                            <button
                                class="bg-brand mt-4 flex w-full items-center justify-center gap-2 rounded-2xl px-4 py-3.5 text-base font-semibold text-white shadow-lg"
                                @click="toggleOffline"
                            >
                                {{ isOffline ? 'Remove from Device' : 'Download for Offline' }}
                            </button>

                            <button class="bg-surface ring-app text-app mt-3 flex w-full items-center justify-center gap-2 rounded-2xl px-4 py-3.5 font-medium ring-1">
                                Open <span class="text-muted">▾</span>
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

                        <div class="bg-surface ring-app overflow-hidden rounded-2xl ring-1 lg:hidden">
                            <h2 class="border-app border-b px-4 py-3 text-sm font-semibold">Information</h2>
                            <dl>
                                <div
                                    v-for="row in infoRows"
                                    :key="row.label"
                                    class="border-app grid grid-cols-[110px_1fr] gap-3 border-b px-4 py-3 text-sm last:border-0"
                                >
                                    <dt class="text-muted">{{ row.label }}</dt>
                                    <dd class="text-app font-medium">{{ row.value }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="bg-surface ring-app hidden overflow-hidden rounded-2xl ring-1 lg:block">
                            <h2 class="border-app border-b px-6 py-4 text-sm font-semibold">Information</h2>
                            <dl class="grid gap-0 sm:grid-cols-2">
                                <div
                                    v-for="row in infoRows"
                                    :key="row.label"
                                    class="border-app grid grid-cols-[120px_1fr] gap-3 border-b px-6 py-4 text-sm last:border-0"
                                >
                                    <dt class="text-muted">{{ row.label }}</dt>
                                    <dd class="text-app font-medium">{{ row.value }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <aside class="mt-8 space-y-4 lg:sticky lg:top-24 lg:mt-0">
                        <div class="bg-surface ring-app rounded-2xl p-4 ring-1 lg:p-5">
                            <div class="flex items-start gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-300">✓</span>
                                <div class="flex-1">
                                    <p class="font-semibold">Offline</p>
                                    <p class="text-muted mt-1 text-sm">
                                        {{ isOffline ? 'Available without an internet connection.' : 'Download to read without internet.' }}
                                    </p>
                                    <button
                                        class="border-app text-app mt-3 w-full rounded-xl border px-4 py-2.5 text-sm font-medium"
                                        @click="toggleOffline"
                                    >
                                        {{ isOffline ? 'Remove from Device' : 'Download for Offline' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="bg-surface ring-app overflow-hidden rounded-2xl ring-1">
                            <h2 class="border-app border-b px-4 py-3 text-sm font-semibold lg:px-5">You might also like</h2>
                            <div class="divide-app divide-y">
                                <div
                                    v-for="related in relatedResources"
                                    :key="related.title"
                                    class="flex items-center gap-3 px-4 py-3 lg:px-5"
                                >
                                    <div class="bg-surface-muted h-12 w-9 shrink-0 rounded" />
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium">{{ related.title }}</p>
                                        <p class="text-muted truncate text-xs">{{ related.author }}</p>
                                    </div>
                                    <button class="text-brand">↓</button>
                                </div>
                            </div>
                        </div>

                        <div v-if="resource.files?.length" class="bg-surface ring-app overflow-hidden rounded-2xl ring-1">
                            <h2 class="border-app border-b px-4 py-3 text-sm font-semibold lg:px-5">Files</h2>
                            <div
                                v-for="file in resource.files"
                                :key="file.id"
                                class="flex items-center justify-between px-4 py-3 lg:px-5"
                            >
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium">{{ file.file_name }}</p>
                                    <p class="text-muted text-xs">{{ formatSize(file.file_size) }}</p>
                                </div>
                                <button class="text-brand">↓</button>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </template>
    </AppShell>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppShell from '@/layouts/AppShell.vue';
import ResourceDetailSkeleton from '@/components/resources/ResourceDetailSkeleton.vue';
import IconBack from '@/components/icons/IconBack.vue';
import IconCloudOffline from '@/components/icons/IconCloudOffline.vue';
import IconBookmark from '@/components/icons/IconBookmark.vue';
import { useLibrary } from '@/composables/useLibrary';
import { useResourceCache } from '@/composables/useResourceCache';
import { useDelayedLoading } from '@/composables/useDelayedLoading';

const route = useRoute();
const router = useRouter();
const library = useLibrary();
const { getResource, fetchResource } = useResourceCache();

const resource = ref(null);
const loading = ref(true);
const error = ref(null);
const showFullDescription = ref(false);

const needsSkeleton = computed(() => loading.value && ! resource.value);
const { showSkeleton } = useDelayedLoading(needsSkeleton);

function resolveInitialResource() {
    return history.state?.resource ?? getResource(route.params.slug);
}

async function loadResource(slug) {
    error.value = null;
    const cached = resolveInitialResource();

    if (cached) {
        resource.value = cached;
        loading.value = false;
    } else {
        loading.value = true;
    }

    try {
        const detail = await fetchResource(slug);
        resource.value = detail;
    } catch {
        if (! resource.value) {
            error.value = 'Could not load this resource. Check your connection and try again.';
        }
    } finally {
        loading.value = false;
    }
}

const isOffline = computed(() => resource.value && library.isDownloaded(resource.value.slug));
const primaryAuthor = computed(() => resource.value?.authors?.[0]?.name ?? null);

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

const relatedResources = [
    { title: 'REPS Guidelines Handbook', author: 'Rosalie Orma' },
    { title: 'DOST GIA Monitoring and Evaluation Manual', author: 'Czarlina May Magnata' },
];

function formatSize(bytes) {
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function toggleOffline() {
    if (!resource.value) return;
    if (isOffline.value) {
        library.state.downloadedSlugs.delete(resource.value.slug);
    } else {
        library.markDownloaded(resource.value.slug);
        window.axios.post('/api/v1/downloads', { resource_id: resource.value.id }).catch(() => {});
    }
}

onMounted(() => loadResource(route.params.slug));

watch(() => route.params.slug, (slug) => {
    if (! slug) {
        return;
    }

    error.value = null;
    resource.value = resolveInitialResource();
    loading.value = ! resource.value;
    loadResource(slug);
});
</script>
