<template>
    <AppShell>
        <LibraryDrawer />

        <div class="tablet-main tablet-sidebar:flex tablet-sidebar:h-[100dvh] tablet-sidebar:max-h-[100dvh] tablet-sidebar:overflow-hidden">
            <!-- Sidebar: tablet landscape and wider (portrait uses drawer) -->
            <div class="bg-sidebar border-app hidden tablet-sidebar:flex tablet-sidebar:h-full tablet-sidebar:w-64 tablet-sidebar:shrink-0 tablet-sidebar:flex-col tablet-sidebar:overflow-hidden tablet-sidebar:border-r xl:w-72">
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
                <header class="bg-surface border-app hidden shrink-0 border-b px-6 py-5 tablet-sidebar:block xl:px-8 xl:py-6">
                    <div class="flex items-end justify-between">
                        <div>
                            <h1 class="text-app text-3xl font-bold">{{ sectionTitle }}</h1>
                            <p class="text-muted mt-1">{{ sectionSubtitle }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button
                                class="bg-surface-muted ring-app flex h-10 w-10 items-center justify-center rounded-full ring-1"
                                @click="router.push('/discover')"
                            >
                                <IconSearch class="h-5 w-5" />
                            </button>
                            <select
                                v-model="sortBy"
                                class="bg-surface ring-app rounded-xl border-0 px-4 py-2.5 text-sm ring-1"
                            >
                                <option value="recent">Sort by: Recent</option>
                                <option value="title">Sort by: Title</option>
                            </select>
                        </div>
                    </div>
                </header>

                <div class="flex min-h-0 flex-1 flex-col tablet-sidebar:overflow-hidden">
                    <div class="page-content px-4 pt-5 tablet-sidebar:flex tablet-sidebar:min-h-0 tablet-sidebar:flex-1 tablet-sidebar:flex-col tablet-sidebar:overflow-hidden tablet-sidebar:px-8 tablet-sidebar:pt-6 tablet-sidebar:pb-0">
                        <div class="mb-3 flex shrink-0 items-center justify-end tablet-sidebar:hidden">
                            <select
                                v-model="sortBy"
                                class="bg-surface ring-app rounded-xl border-0 px-3 py-2 text-sm shadow-sm ring-1"
                            >
                                <option value="recent">Sort by: Recent</option>
                                <option value="title">Sort by: Title</option>
                            </select>
                        </div>

                        <FilterChips v-model="activeFilter" :filters="filters" class="mb-5 shrink-0 tablet-sidebar:mb-5 tablet-sidebar:pt-2" />

                        <div class="library-scroll tablet-sidebar:min-h-0 tablet-sidebar:flex-1 tablet-sidebar:overflow-y-auto tablet-sidebar:pt-1">
                            <div v-if="loading" class="text-muted py-16 text-center text-sm">
                                Loading your library...
                            </div>

                            <div
                                v-else
                                class="grid grid-cols-2 gap-3 sm:grid-cols-3 tablet-sidebar:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5"
                            >
                                <ResourceCard
                                    v-for="resource in filteredResources"
                                    :key="resource.id"
                                    :resource="resource"
                                    :offline="library.isDownloaded(resource.slug)"
                                />
                            </div>

                            <div
                                v-if="!loading && filteredResources.length === 0"
                                class="bg-surface ring-app mt-8 rounded-2xl p-8 text-center shadow-sm ring-1"
                            >
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
import ResourceCard from '@/components/resources/ResourceCard.vue';
import IconCloudOffline from '@/components/icons/IconCloudOffline.vue';
import IconSearch from '@/components/icons/IconSearch.vue';
import { useLibrary } from '@/composables/useLibrary';

const router = useRouter();
const library = useLibrary();

const resources = ref([]);
const loading = ref(true);
const activeFilter = ref('All');
const sortBy = ref('recent');
const filters = ['All', 'Guidelines', 'PDFs', 'Videos', 'Audio', 'Others'];

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
    }

    return list;
});

watch(() => library.state.activeSection, () => {
    document.querySelector('.library-scroll')?.scrollTo({ top: 0, behavior: 'smooth' });
});

onMounted(async () => {
    try {
        const response = await window.axios.get('/api/v1/resources');
        resources.value = response.data.data ?? [];
    } catch {
        resources.value = [];
    } finally {
        loading.value = false;
    }
});
</script>
