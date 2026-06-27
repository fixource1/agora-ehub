<template>
    <AppShell>
        <div class="discover-main flex min-h-0 flex-1 flex-col tablet-sidebar:overflow-hidden">
            <MobileTopBar
                class="tablet-sidebar:hidden"
                title="Discover"
                subtitle="Explore institutional knowledge"
                :show-search="true"
                @search="focusSearch"
            />

            <header class="bg-surface border-app safe-top hidden shrink-0 border-b px-4 pb-4 pt-1 tablet-sidebar:block lg:px-6 lg:pb-5 xl:px-8 xl:py-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div class="min-w-0">
                        <h1 class="text-app text-2xl font-bold lg:text-3xl">Discover</h1>
                        <p class="text-muted mt-0.5 text-sm lg:mt-1">Explore institutional knowledge</p>
                    </div>
                    <div class="bg-surface-muted ring-app flex w-full items-center gap-3 rounded-xl px-4 py-3 ring-1 lg:max-w-md">
                        <IconSearch class="text-muted h-5 w-5 shrink-0" />
                        <input
                            ref="searchInput"
                            v-model="query"
                            type="search"
                            placeholder="Search title, author, keyword..."
                            class="text-app w-full bg-transparent text-sm outline-none"
                        >
                    </div>
                </div>
            </header>

            <div class="flex min-h-0 flex-1 flex-col tablet-sidebar:overflow-hidden">
                <div class="page-content px-4 pt-5 tablet-sidebar:flex tablet-sidebar:min-h-0 tablet-sidebar:flex-1 tablet-sidebar:flex-col tablet-sidebar:overflow-hidden tablet-sidebar:px-4 tablet-sidebar:pt-4 tablet-sidebar:pb-0 lg:px-6 lg:pt-6 xl:px-8">
                    <div class="discover-scroll flex min-w-0 flex-col gap-6 tablet-sidebar:min-h-0 tablet-sidebar:flex-1 tablet-sidebar:overflow-y-auto">
                        <div class="bg-surface ring-app rounded-2xl p-3 ring-1 tablet-sidebar:hidden">
                            <div class="bg-surface-muted flex items-center gap-3 rounded-xl px-4 py-2.5">
                                <IconSearch class="text-muted h-5 w-5 shrink-0" />
                                <input
                                    ref="mobileSearchInput"
                                    v-model="query"
                                    type="search"
                                    placeholder="Search title, author, keyword..."
                                    class="text-app w-full bg-transparent text-sm outline-none"
                                >
                            </div>
                        </div>

                        <section class="min-w-0">
                            <h2 class="text-muted text-xs font-semibold uppercase tracking-wide sm:text-sm">Popular Categories</h2>
                            <div class="discover-categories scrollbar-hide mt-2">
                                <button
                                    v-for="cat in categories"
                                    :key="cat.slug"
                                    type="button"
                                    class="bg-surface ring-app rounded-full px-3.5 py-1.5 text-sm ring-1 transition hover:bg-surface-muted sm:px-4 sm:py-2"
                                    :class="activeCategory === cat.slug ? 'bg-maroon text-white ring-0' : ''"
                                    @click="toggleCategory(cat.slug)"
                                >
                                    {{ cat.name }}
                                </button>
                            </div>
                        </section>

                        <section class="discover-panel min-w-0">
                            <LoadErrorBanner
                                v-if="error"
                                class="mb-4"
                                :error="error"
                                @retry="retry"
                            />

                            <ResourceCollection
                                :resources="resources"
                                :loading="showSkeleton"
                            >
                                <template #toolbar>
                                    <div class="resource-toolbar">
                                        <div class="min-w-0">
                                            <h2 class="text-app text-base font-semibold sm:text-lg">Recently Added</h2>
                                            <SkeletonBlock v-if="showSkeleton" class="mt-1 h-3 w-24" />
                                            <p v-else class="text-muted text-xs sm:text-sm">{{ resources.length }} resources</p>
                                        </div>
                                        <div class="resource-toolbar-actions">
                                            <ResourceViewToggle />
                                        </div>
                                    </div>
                                </template>

                                <template #empty>
                                    <div class="resource-empty-state">
                                        <p class="text-app font-medium">No resources found</p>
                                        <p class="text-muted mt-2 text-sm">Try a different search or category.</p>
                                    </div>
                                </template>
                            </ResourceCollection>

                            <div v-if="hasMore" class="mt-6 flex justify-center pb-4">
                                <button
                                    type="button"
                                    class="bg-surface ring-app rounded-xl px-5 py-2.5 text-sm font-medium ring-1"
                                    :disabled="loadingMore"
                                    @click="loadMore"
                                >
                                    {{ loadingMore ? 'Loading…' : 'Load more' }}
                                </button>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import AppShell from '@/layouts/AppShell.vue';
import MobileTopBar from '@/components/layout/MobileTopBar.vue';
import IconSearch from '@/components/icons/IconSearch.vue';
import ResourceCollection from '@/components/resources/ResourceCollection.vue';
import ResourceViewToggle from '@/components/resources/ResourceViewToggle.vue';
import SkeletonBlock from '@/components/skeleton/SkeletonBlock.vue';
import LoadErrorBanner from '@/components/ui/LoadErrorBanner.vue';
import { useDelayedLoading } from '@/composables/useDelayedLoading';
import { useResourcesList } from '@/composables/useResourcesList';

const query = ref('');
const { resources, loading, loadingMore, error, hasMore, load, loadMore, setFilters, retry } = useResourcesList();
const { showSkeleton } = useDelayedLoading(loading);
const activeCategory = ref(null);
const searchInput = ref(null);
const mobileSearchInput = ref(null);

const categories = [
    { name: 'Guidelines', slug: 'guidelines' },
    { name: 'Research', slug: 'research' },
    { name: 'Training Materials', slug: 'training-materials' },
    { name: 'Science', slug: 'science' },
    { name: 'Education', slug: 'education' },
];

let searchTimer = null;

function toggleCategory(slug) {
    activeCategory.value = activeCategory.value === slug ? null : slug;
}

function applyFilters() {
    const params = {};

    if (query.value.trim()) {
        params.q = query.value.trim();
    }

    if (activeCategory.value) {
        params.category = activeCategory.value;
    }

    setFilters(params);
}

function focusSearch() {
    mobileSearchInput.value?.focus();
}

watch([query, activeCategory], () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 300);
});

onMounted(() => load());
</script>
