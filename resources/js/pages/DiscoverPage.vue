<template>
    <AppShell>
        <div class="discover-main tablet-sidebar:flex tablet-sidebar:h-[100dvh] tablet-sidebar:max-h-[100dvh] tablet-sidebar:flex-col tablet-sidebar:overflow-hidden">
            <MobileTopBar
                class="tablet-sidebar:hidden"
                title="Discover"
                subtitle="Explore institutional knowledge"
                :show-search="true"
                @search="focusSearch"
            />

            <header class="bg-surface border-app hidden shrink-0 border-b px-4 py-4 tablet-sidebar:block lg:px-6 lg:py-5 xl:px-8 xl:py-6">
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
                                    :key="cat"
                                    type="button"
                                    class="bg-surface ring-app rounded-full px-3.5 py-1.5 text-sm ring-1 transition hover:bg-surface-muted sm:px-4 sm:py-2"
                                    :class="activeCategory === cat ? 'bg-maroon text-white ring-0' : ''"
                                    @click="activeCategory = activeCategory === cat ? null : cat"
                                >
                                    {{ cat }}
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
                                :resources="filteredResources"
                                :loading="showSkeleton"
                            >
                                <template #toolbar>
                                    <div class="resource-toolbar">
                                        <div class="min-w-0">
                                            <h2 class="text-app text-base font-semibold sm:text-lg">Recently Added</h2>
                                            <SkeletonBlock v-if="showSkeleton" class="mt-1 h-3 w-24" />
                                            <p v-else class="text-muted text-xs sm:text-sm">{{ filteredResources.length }} resources</p>
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
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
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
const { resources, loading, error, load, retry } = useResourcesList();
const { showSkeleton } = useDelayedLoading(loading);
const activeCategory = ref(null);
const searchInput = ref(null);
const mobileSearchInput = ref(null);

const categories = ['Guidelines', 'Research', 'Training Materials', 'Science', 'Education'];

const filteredResources = computed(() => {
    let list = [...resources.value];
    const q = query.value.trim().toLowerCase();

    if (q) {
        list = list.filter((r) =>
            r.title?.toLowerCase().includes(q)
            || r.description?.toLowerCase().includes(q)
            || r.authors?.some((a) => a.name?.toLowerCase().includes(q)),
        );
    }

    if (activeCategory.value) {
        list = list.filter((r) =>
            r.category?.name?.toLowerCase() === activeCategory.value.toLowerCase(),
        );
    }

    list.sort((a, b) => new Date(b.published_at ?? b.created_at ?? 0) - new Date(a.published_at ?? a.created_at ?? 0));

    return list;
});

function focusSearch() {
    mobileSearchInput.value?.focus();
}

onMounted(() => load());
</script>
