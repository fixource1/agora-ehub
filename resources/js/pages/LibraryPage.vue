<template>
    <AppShell>
        <LibraryDrawer />

        <div class="tablet-main tablet-sidebar:flex tablet-sidebar:h-[100dvh] tablet-sidebar:max-h-[100dvh] tablet-sidebar:overflow-hidden">
            <!-- Sidebar: tablet landscape and wider (portrait uses drawer) -->
            <div class="bg-sidebar border-app hidden tablet-sidebar:flex tablet-sidebar:h-full tablet-sidebar:w-56 tablet-sidebar:shrink-0 tablet-sidebar:flex-col tablet-sidebar:overflow-hidden tablet-sidebar:border-r lg:w-64 xl:w-72">
                <LibrarySidebar embedded />
            </div>

            <div class="bg-app flex min-w-0 flex-1 flex-col tablet-sidebar:h-full tablet-sidebar:overflow-hidden">
                <!-- Mobile / tablet portrait top bar -->
                <MobileTopBar
                    class="tablet-sidebar:hidden"
                    :show-menu="true"
                    :show-search="true"
                    :title="sectionTitle"
                    :subtitle="sectionSubtitle"
                    @search="router.push('/discover')"
                />

                <!-- Tablet landscape / desktop header -->
                <header class="bg-surface border-app hidden shrink-0 border-b px-4 py-4 tablet-sidebar:block lg:px-6 lg:py-5 xl:px-8 xl:py-6">
                    <div class="flex items-end justify-between gap-3">
                        <div class="min-w-0">
                            <h1 class="text-app text-2xl font-bold lg:text-3xl">{{ sectionTitle }}</h1>
                            <p class="text-muted mt-0.5 text-sm lg:mt-1">{{ sectionSubtitle }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button
                                class="bg-surface-muted ring-app flex h-10 w-10 items-center justify-center rounded-full ring-1"
                                @click="router.push('/discover')"
                            >
                                <IconSearch class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </header>

                <div class="flex min-h-0 flex-1 flex-col tablet-sidebar:overflow-hidden">
                    <div class="page-content px-4 pt-5 tablet-sidebar:flex tablet-sidebar:min-h-0 tablet-sidebar:flex-1 tablet-sidebar:flex-col tablet-sidebar:overflow-hidden tablet-sidebar:px-4 tablet-sidebar:pt-4 tablet-sidebar:pb-0 lg:px-6 lg:pt-6 xl:px-8">
                        <FilterChips v-model="activeFilter" :filters="filters" class="mb-4 shrink-0 tablet-sidebar:mb-4 tablet-sidebar:pt-2" />

                        <LoadErrorBanner
                            v-if="error"
                            class="mb-4 shrink-0"
                            :error="error"
                            @retry="retry"
                        />

                        <div class="library-scroll tablet-sidebar:min-h-0 tablet-sidebar:flex-1 tablet-sidebar:overflow-y-auto tablet-sidebar:pt-1">
                            <ResourceCollection
                                :resources="filteredResources"
                                :loading="showSkeleton"
                                :offline="(resource) => library.isDownloaded(resource.slug)"
                            >
                                <template #toolbar>
                                    <div class="resource-toolbar">
                                        <div class="min-w-0">
                                            <template v-if="showSkeleton">
                                                <SkeletonBlock class="h-4 w-28" />
                                                <SkeletonBlock class="mt-2 h-3 w-36" />
                                            </template>
                                            <template v-else>
                                                <p class="text-app text-sm font-medium sm:text-base">
                                                    {{ filteredResources.length }} resources
                                                </p>
                                                <p
                                                    v-if="library.state.activeSection === 'recent'"
                                                    class="text-muted text-xs"
                                                >
                                                    Most recently opened first
                                                </p>
                                            </template>
                                        </div>
                                        <div class="resource-toolbar-actions">
                                            <ResourceViewToggle />
                                            <ResourceSortSelect
                                                v-if="showSort"
                                                v-model="sortBy"
                                            />
                                        </div>
                                    </div>
                                </template>

                                <template #empty>
                                    <div class="resource-empty-state">
                                        <IconCloudOffline class="mx-auto mb-3 h-10 w-10 text-primary-400" />
                                        <p class="text-app font-medium">{{ emptyState.title }}</p>
                                        <p class="text-muted mt-2 text-sm">
                                            {{ emptyState.body }}
                                        </p>
                                        <RouterLink
                                            v-if="emptyState.showDiscover"
                                            to="/discover"
                                            class="btn-maroon mt-4 inline-block rounded-xl px-5 py-2.5 text-sm font-medium"
                                        >
                                            Discover Resources
                                        </RouterLink>
                                    </div>
                                </template>
                            </ResourceCollection>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import AppShell from '@/layouts/AppShell.vue';
import MobileTopBar from '@/components/layout/MobileTopBar.vue';
import LibraryDrawer from '@/components/library/LibraryDrawer.vue';
import LibrarySidebar from '@/components/library/LibrarySidebar.vue';
import FilterChips from '@/components/library/FilterChips.vue';
import ResourceCollection from '@/components/resources/ResourceCollection.vue';
import ResourceViewToggle from '@/components/resources/ResourceViewToggle.vue';
import ResourceSortSelect from '@/components/resources/ResourceSortSelect.vue';
import IconCloudOffline from '@/components/icons/IconCloudOffline.vue';
import IconSearch from '@/components/icons/IconSearch.vue';
import SkeletonBlock from '@/components/skeleton/SkeletonBlock.vue';
import LoadErrorBanner from '@/components/ui/LoadErrorBanner.vue';
import { useDelayedLoading } from '@/composables/useDelayedLoading';
import { useLibrary } from '@/composables/useLibrary';
import { useResourcesList } from '@/composables/useResourcesList';

const router = useRouter();
const library = useLibrary();
const { resources, loading, error, load, retry } = useResourcesList();
const { showSkeleton } = useDelayedLoading(loading);
const activeFilter = ref('All');
const sortBy = ref('recent');
const filters = ['All', 'Guidelines', 'PDFs', 'Videos', 'Audio', 'Others'];

const showSort = computed(() => !['recent', 'offline', 'bookmarks', 'notes'].includes(library.state.activeSection));

const sectionTitle = computed(() => {
    const map = {
        offline: 'Offline',
        all: 'All Resources',
        notes: 'Notes',
        bookmarks: 'Bookmarks',
        recent: 'Recently Opened',
    };
    return map[library.state.activeSection] ?? 'My Library';
});

const sectionSubtitle = computed(() => {
    const map = {
        offline: 'Available without internet connection',
        all: 'Your personal knowledge collection',
        notes: 'Annotations and highlights across resources',
        bookmarks: 'Pages and sections you saved',
        recent: 'Resources you opened lately',
    };
    return map[library.state.activeSection] ?? 'Your personal knowledge collection';
});

const emptyState = computed(() => {
    const map = {
        offline: {
            title: 'No offline resources yet',
            body: 'Browse Discover to find materials and download them for offline reading.',
            showDiscover: true,
        },
        bookmarks: {
            title: 'No bookmarks yet',
            body: 'Bookmark pages while reading to find them here quickly.',
            showDiscover: false,
        },
        notes: {
            title: 'No notes yet',
            body: 'Add notes while reading to keep your thoughts organized.',
            showDiscover: false,
        },
        recent: {
            title: 'Nothing opened recently',
            body: 'Resources you open will appear here for quick access.',
            showDiscover: true,
        },
        all: {
            title: 'No resources found',
            body: 'Try a different filter or browse Discover for new materials.',
            showDiscover: true,
        },
    };
    return map[library.state.activeSection] ?? map.all;
});

const filteredResources = computed(() => {
    let list = library.filterBySection([...resources.value]);

    if (activeFilter.value === 'Guidelines') {
        list = list.filter((r) => r.category?.slug === 'guidelines' || r.category?.name === 'Guidelines');
    } else if (activeFilter.value === 'Books') {
        list = list.filter((r) => ['ebook', 'journal'].includes(r.resource_type?.slug));
    } else if (activeFilter.value === 'PDFs') {
        list = list.filter((r) => r.resource_type?.slug === 'pdf-document');
    } else if (activeFilter.value === 'Videos') {
        list = list.filter((r) => r.resource_type?.slug === 'video');
    } else if (activeFilter.value === 'Audio') {
        list = list.filter((r) => r.resource_type?.slug === 'audio');
    } else if (activeFilter.value === 'Others') {
        list = list.filter((r) => r.resource_type?.slug === 'other');
    }

    if (sortBy.value === 'title') {
        list.sort((a, b) => a.title.localeCompare(b.title));
    } else if (sortBy.value === 'recent' && library.state.activeSection === 'all') {
        list.sort((a, b) => new Date(b.published_at ?? b.created_at ?? 0) - new Date(a.published_at ?? a.created_at ?? 0));
    }

    return list;
});

watch(() => library.state.activeSection, () => {
    document.querySelector('.library-scroll')?.scrollTo({ top: 0, behavior: 'smooth' });
});

onMounted(() => load());
</script>
