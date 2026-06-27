<template>
    <AppShell>
        <div class="library-page-root">
            <LibraryDrawer />

            <div class="library-page-layout">
                <div class="library-sidebar-shell border-app bg-sidebar hidden tablet-sidebar:flex tablet-sidebar:w-56 tablet-sidebar:flex-col tablet-sidebar:border-r lg:w-64 xl:w-72">
                    <LibrarySidebar embedded />
                </div>

                <div class="library-main-panel bg-app">
                    <MobileTopBar
                        class="library-fixed-chrome tablet-sidebar:hidden"
                        :show-menu="true"
                        :show-search="true"
                        :title="sectionTitle"
                        :subtitle="sectionSubtitle"
                        @search="router.push('/discover')"
                    />

                    <header class="library-fixed-chrome bg-surface border-app safe-top hidden shrink-0 border-b px-4 pb-4 pt-1 tablet-sidebar:block lg:px-6 lg:pb-5 xl:px-8 xl:py-6">
                        <div class="flex items-end justify-between gap-3">
                            <div class="min-w-0">
                                <h1 class="text-app text-2xl font-bold lg:text-3xl">{{ sectionTitle }}</h1>
                                <p class="text-muted mt-0.5 text-sm lg:mt-1">{{ sectionSubtitle }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <PressableButton
                                    icon
                                    class="bg-surface-muted ring-app flex h-10 w-10 items-center justify-center rounded-full ring-1"
                                    aria-label="Search resources"
                                    @click="router.push('/discover')"
                                >
                                    <IconSearch class="h-5 w-5" />
                                </PressableButton>
                            </div>
                        </div>
                    </header>

                    <div class="library-fixed-chrome library-content-chrome px-4 pt-5 tablet-sidebar:px-4 tablet-sidebar:pt-4 lg:px-6 lg:pt-6 xl:px-8">
                        <FilterChips v-model="activeFilter" :filters="filters" class="mb-4 tablet-sidebar:mb-4 tablet-sidebar:pt-2" />

                        <LoadErrorBanner
                            v-if="showLoadError"
                            class="mb-4"
                            :error="error"
                            @retry="retry"
                        />

                        <LibraryResourceToolbar
                            class="mb-4 tablet-sidebar:mb-4"
                            :loading="showSkeleton"
                            :count="resourceCount"
                            :show-recent-hint="library.state.activeSection === 'recent'"
                            :show-sort="showSort"
                            v-model:sort-by="sortBy"
                        />
                    </div>

                    <div class="library-scroll-region px-4 pb-page-nav tablet-sidebar:px-4 lg:px-6 xl:px-8">
                        <ResourceCollection
                            :resources="filteredResources"
                            :loading="showSkeleton"
                            :offline="(resource) => library.isDownloaded(resource.slug)"
                            :show-toolbar="false"
                        >
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

                        <div v-if="showLoadMore" class="mt-6 flex justify-center pb-4">
                            <button
                                type="button"
                                class="bg-surface ring-app rounded-xl px-5 py-2.5 text-sm font-medium ring-1"
                                :disabled="loadingMore"
                                @click="loadMore"
                            >
                                {{ loadingMore ? 'Loading…' : 'Load more' }}
                            </button>
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
import LibraryResourceToolbar from '@/components/library/LibraryResourceToolbar.vue';
import ResourceCollection from '@/components/resources/ResourceCollection.vue';
import PressableButton from '@/components/ui/PressableButton.vue';
import IconCloudOffline from '@/components/icons/IconCloudOffline.vue';
import IconSearch from '@/components/icons/IconSearch.vue';
import LoadErrorBanner from '@/components/ui/LoadErrorBanner.vue';
import { useDelayedLoading } from '@/composables/useDelayedLoading';
import { useLibrary } from '@/composables/useLibrary';
import { getAllOfflineResources } from '@/composables/useOfflineStore';
import { useResourcesList } from '@/composables/useResourcesList';

const router = useRouter();
const library = useLibrary();
const { resources, loading, loadingMore, error, hasMore, total, load, loadMore, setFilters, retry } = useResourcesList();
const { showSkeleton } = useDelayedLoading(loading);
const activeFilter = ref('All');
const sortBy = ref('recent');
const filters = ['All', 'Guidelines', 'PDFs', 'Videos', 'Audio', 'Others'];
const offlineResources = ref([]);

const usesServerFilters = computed(() => (
    library.state.activeSection === 'all' && ! activeCollection.value
));

const showLoadMore = computed(() => usesServerFilters.value && hasMore.value);

const resourceCount = computed(() => (
    usesServerFilters.value && total.value != null ? total.value : filteredResources.value.length
));

const filterApiParams = {
    All: {},
    Guidelines: { category: 'guidelines' },
    PDFs: { type: 'pdf-document' },
    Videos: { type: 'video' },
    Audio: { type: 'audio' },
    Others: { type: 'other' },
};

const showSort = computed(() => !['recent', 'offline', 'bookmarks', 'notes'].includes(library.state.activeSection));

const showLoadError = computed(() => {
    if (library.state.activeSection === 'offline' && offlineResources.value.length > 0) {
        return false;
    }

    return Boolean(error.value);
});

const activeCollection = computed(() => library.getActiveCollection());

const sectionTitle = computed(() => {
    if (activeCollection.value) {
        return activeCollection.value.name;
    }

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
    if (activeCollection.value) {
        return 'Resources you saved in this collection';
    }

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
    if (activeCollection.value) {
        return {
            title: `No resources in ${activeCollection.value.name}`,
            body: 'Open a resource and use More → Add to collection, or browse Discover to find materials.',
            showDiscover: true,
        };
    }

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
    const source = library.state.activeSection === 'offline'
        ? offlineResources.value
        : library.filterBySection([...resources.value]);

    let list = [...source];

    if (! usesServerFilters.value) {
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
    }

    if (sortBy.value === 'title') {
        list.sort((a, b) => a.title.localeCompare(b.title));
    } else if (sortBy.value === 'recent' && (library.state.activeSection === 'all' || activeCollection.value)) {
        list.sort((a, b) => new Date(b.published_at ?? b.created_at ?? 0) - new Date(a.published_at ?? a.created_at ?? 0));
    }

    return list;
});

watch(() => library.state.activeSection, () => {
    document.querySelector('.library-scroll-region')?.scrollTo({ top: 0, behavior: 'smooth' });
});

watch(activeFilter, (filter) => {
    if (! usesServerFilters.value) {
        return;
    }

    setFilters(filterApiParams[filter] ?? {});
});

watch(usesServerFilters, (enabled) => {
    if (enabled) {
        setFilters(filterApiParams[activeFilter.value] ?? {});
    } else {
        load({ force: true, params: {} });
    }
});

async function refreshOfflineResources() {
    offlineResources.value = await getAllOfflineResources();
}

watch(() => library.state.downloadedSlugs.size, () => {
    refreshOfflineResources();
}, { immediate: true });

onMounted(async () => {
    await Promise.all([
        usesServerFilters.value
            ? setFilters(filterApiParams[activeFilter.value] ?? {})
            : load(),
        refreshOfflineResources(),
    ]);
});
</script>
