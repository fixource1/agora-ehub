<template>
    <AppShell>
        <HomeHeader />

        <div class="page-content mx-auto max-w-5xl px-4 pt-5 pb-4 md:px-8 md:pt-6">
            <div class="home-hero overflow-hidden rounded-3xl p-6 shadow-lg md:p-8">
                <h2 class="text-2xl font-bold leading-tight text-white md:text-3xl">
                    {{ APP_HERO_HEADLINE }}
                </h2>
                <p class="home-hero-body mt-3 max-w-xl text-sm leading-6 md:text-base">
                    {{ APP_TAGLINE }}
                </p>
            </div>

            <LoadErrorBanner
                v-if="error"
                class="mt-5"
                :error="error"
                @retry="retry"
            />

            <HomeStatsSkeleton v-if="showSkeleton" class="mt-5" />
            <dl v-else-if="resources.length" class="home-stats mt-5 grid grid-cols-3 gap-3 md:gap-4">
                <div class="border-app bg-surface rounded-2xl border px-3 py-3 md:px-4 md:py-4">
                    <dt class="text-muted text-xs">Resources</dt>
                    <dd class="text-app mt-1 text-lg font-semibold md:text-xl">{{ resources.length }}</dd>
                </div>
                <div class="border-app bg-surface rounded-2xl border px-3 py-3 md:px-4 md:py-4">
                    <dt class="text-muted text-xs">Offline</dt>
                    <dd class="text-app mt-1 text-lg font-semibold md:text-xl">{{ offlineResources.length }}</dd>
                </div>
                <div class="border-app bg-surface rounded-2xl border px-3 py-3 md:px-4 md:py-4">
                    <dt class="text-muted text-xs">Bookmarks</dt>
                    <dd class="text-app mt-1 text-lg font-semibold md:text-xl">{{ library.state.bookmarkSlugs.size }}</dd>
                </div>
            </dl>

            <HomeResourceRail
                v-if="continueReading.length || showSkeleton"
                class="mt-6"
                title="Continue reading"
                subtitle="Pick up where you left off"
                :resources="continueReading"
                :loading="showSkeleton"
                :offline="library.isDownloaded"
                see-all-label="See all"
                @see-all="openLibrary('recent')"
            />

            <HomeResourceRail
                v-if="offlineResources.length"
                title="On your device"
                subtitle="Available without internet"
                :resources="offlineResources"
                :offline="library.isDownloaded"
                see-all-label="See all"
                @see-all="openLibrary('offline')"
            />

            <HomeResourceRail
                title="Recently added"
                subtitle="Latest from UPLB OVCRE"
                :resources="recentlyAdded"
                :loading="showSkeleton"
                :offline="library.isDownloaded"
                see-all-label="Browse all"
                @see-all="router.push('/discover')"
            />
        </div>
    </AppShell>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AppShell from '@/layouts/AppShell.vue';
import HomeHeader from '@/components/layout/HomeHeader.vue';
import HomeResourceRail from '@/components/home/HomeResourceRail.vue';
import HomeStatsSkeleton from '@/components/skeleton/HomeStatsSkeleton.vue';
import LoadErrorBanner from '@/components/ui/LoadErrorBanner.vue';
import { useDelayedLoading } from '@/composables/useDelayedLoading';
import { useLibrary } from '@/composables/useLibrary';
import { useResourcesList } from '@/composables/useResourcesList';
import { APP_HERO_HEADLINE, APP_TAGLINE } from '@/constants/brand';

const router = useRouter();
const library = useLibrary();
const { resources, loading, error, load, retry } = useResourcesList();
const { showSkeleton } = useDelayedLoading(loading);

const continueReading = computed(() => library.filterBySection([...resources.value]).slice(0, 8));

const offlineResources = computed(() =>
    resources.value.filter((resource) => library.isDownloaded(resource.slug)).slice(0, 8),
);

const recentlyAdded = computed(() =>
    [...resources.value]
        .sort((a, b) => new Date(b.published_at ?? b.created_at ?? 0) - new Date(a.published_at ?? a.created_at ?? 0))
        .slice(0, 8),
);

function openLibrary(section) {
    library.setActiveSection(section);
    router.push('/library');
}

onMounted(() => load());
</script>
