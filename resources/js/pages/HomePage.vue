<template>
    <AppShell>
        <MobileTopBar
            :title="SALIKSIC_NAME"
            :subtitle="SALIKSIC_TAGLINE_SHORT"
            :show-logo="true"
        />

        <div class="page-content mx-auto max-w-5xl px-4 pt-6 md:px-8 md:pt-8">
            <div class="home-hero rounded-3xl p-6 shadow-lg md:p-8">
                <p class="home-hero-label text-sm">UPLB AGORA e-Resources</p>
                <h2 class="mt-2 text-2xl font-bold leading-tight text-white md:text-3xl">
                    Your academic knowledge repository
                </h2>
                <p class="home-hero-body mt-3 text-sm leading-6 md:text-base">
                    {{ SALIKSIC_TAGLINE }}
                </p>
                <RouterLink to="/discover" class="home-hero-btn mt-5 inline-block rounded-xl px-5 py-2.5 text-sm font-semibold">
                    Start Exploring
                </RouterLink>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-3 md:gap-4 lg:grid-cols-2">
                <RouterLink
                    to="/library"
                    class="home-action-card bg-surface ring-app rounded-2xl p-4 ring-1 md:p-5"
                >
                    <IconLibrary class="text-brand h-6 w-6 md:h-7 md:w-7" />
                    <p class="text-app mt-3 font-semibold md:text-lg">My Library</p>
                    <p class="text-muted text-xs md:text-sm">4 offline items</p>
                </RouterLink>
                <RouterLink
                    to="/discover"
                    class="home-action-card bg-surface ring-app rounded-2xl p-4 ring-1 md:p-5"
                >
                    <IconDiscover class="text-maroon h-6 w-6 md:h-7 md:w-7" />
                    <p class="text-app mt-3 font-semibold md:text-lg">Discover</p>
                    <p class="text-muted text-xs md:text-sm">Find new resources</p>
                </RouterLink>
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import AppShell from '@/layouts/AppShell.vue';
import MobileTopBar from '@/components/layout/MobileTopBar.vue';
import IconLibrary from '@/components/icons/IconLibrary.vue';
import IconDiscover from '@/components/icons/IconDiscover.vue';
import { SALIKSIC_NAME, SALIKSIC_TAGLINE, SALIKSIC_TAGLINE_SHORT } from '@/constants/brand';
</script>
