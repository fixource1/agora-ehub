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

            <header class="bg-surface border-app hidden shrink-0 border-b px-6 py-5 tablet-sidebar:block xl:px-8 xl:py-6">
                <div class="flex items-end justify-between gap-6">
                    <div>
                        <h1 class="text-app text-3xl font-bold">Discover</h1>
                        <p class="text-muted mt-1">Explore institutional knowledge</p>
                    </div>
                    <div class="bg-surface-muted flex w-full max-w-md items-center gap-3 rounded-xl px-4 py-3 ring-1 ring-app">
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

            <div class="discover-scroll page-content tablet-sidebar:min-h-0 tablet-sidebar:flex-1 tablet-sidebar:overflow-y-auto tablet-sidebar:px-8 tablet-sidebar:pt-6 tablet-sidebar:pb-0">
                <div class="px-4 pt-5 tablet-sidebar:px-0 tablet-sidebar:pt-0">
                    <div class="bg-surface ring-app rounded-2xl p-4 shadow-sm ring-1 tablet-sidebar:hidden">
                        <div class="bg-surface-muted flex items-center gap-3 rounded-xl px-4 py-3">
                            <IconSearch class="text-muted h-5 w-5" />
                            <input
                                ref="mobileSearchInput"
                                v-model="query"
                                type="search"
                                placeholder="Search title, author, keyword..."
                                class="text-app w-full bg-transparent text-sm outline-none"
                            >
                        </div>
                    </div>

                    <section class="mt-6 tablet-sidebar:mt-0">
                        <h2 class="text-muted mb-3 text-sm font-semibold uppercase tracking-wide">Popular Categories</h2>
                        <div class="flex flex-wrap gap-2 tablet-sidebar:gap-3">
                            <button
                                v-for="cat in categories"
                                :key="cat"
                                type="button"
                                class="bg-surface ring-app rounded-full px-4 py-2 text-sm ring-1 transition hover:bg-surface-muted tablet-sidebar:px-5 tablet-sidebar:py-2.5"
                                :class="activeCategory === cat ? 'bg-maroon text-white ring-0' : ''"
                                @click="activeCategory = activeCategory === cat ? null : cat"
                            >
                                {{ cat }}
                            </button>
                        </div>
                    </section>

                    <section class="mt-8 tablet-sidebar:mt-10">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-app text-lg font-semibold tablet-sidebar:text-xl">Recently Added</h2>
                            <p v-if="!loading" class="text-muted text-sm">{{ filteredResources.length }} resources</p>
                        </div>

                        <div v-if="loading" class="text-muted py-16 text-center text-sm">Loading...</div>

                        <div
                            v-else-if="filteredResources.length"
                            class="grid grid-cols-2 gap-3 sm:grid-cols-3 tablet-sidebar:grid-cols-4 xl:grid-cols-5"
                        >
                            <ResourceCard
                                v-for="resource in filteredResources"
                                :key="resource.id"
                                :resource="resource"
                            />
                        </div>

                        <div
                            v-else
                            class="bg-surface ring-app mt-4 rounded-2xl p-8 text-center ring-1"
                        >
                            <p class="text-app font-medium">No resources found</p>
                            <p class="text-muted mt-2 text-sm">Try a different search or category.</p>
                        </div>
                    </section>
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
import ResourceCard from '@/components/resources/ResourceCard.vue';

const query = ref('');
const resources = ref([]);
const loading = ref(true);
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

    return list;
});

function focusSearch() {
    mobileSearchInput.value?.focus();
}

onMounted(async () => {
    try {
        const response = await window.axios.get('/api/v1/resources');
        resources.value = response.data.data ?? [];
    } finally {
        loading.value = false;
    }
});
</script>
