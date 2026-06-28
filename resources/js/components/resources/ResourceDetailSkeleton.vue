<template>
    <div class="resource-detail-page flex min-h-0 flex-1 flex-col overflow-hidden">
        <header class="safe-top bg-surface border-app shrink-0 z-20 flex items-center justify-between border-b px-4 pb-4 pt-1 lg:px-8 lg:pb-5">
            <SkeletonBlock class="h-10 w-10 rounded-full" />
            <div class="flex gap-2">
                <SkeletonBlock class="h-10 w-10 rounded-full" />
                <SkeletonBlock class="h-10 w-10 rounded-full" />
            </div>
        </header>

        <div class="resource-detail-scroll min-h-0 flex-1 overflow-y-auto overscroll-y-contain">
            <div class="page-content-no-nav px-4 pt-6 pb-6 lg:px-8 lg:pt-10 lg:pb-10">
                <div class="lg:grid lg:grid-cols-[280px_minmax(0,1fr)_320px] lg:items-start lg:gap-8 xl:gap-10">
                    <aside class="lg:sticky lg:top-6">
                        <SkeletonBlock class="resource-detail-cover mx-auto aspect-[3/4] w-full max-w-[148px] rounded-2xl sm:max-w-[168px] lg:max-w-[180px]" />
                        <SkeletonBlock class="mx-auto mt-4 h-4 w-32 rounded lg:mx-0" />
                        <SkeletonBlock class="mt-4 h-12 w-full rounded-2xl" />
                        <SkeletonBlock class="mt-3 h-12 w-full rounded-2xl" />
                    </aside>

                    <div class="mt-8 space-y-6 lg:mt-0">
                        <div>
                            <SkeletonBlock class="h-8 w-3/4 max-w-lg rounded lg:h-10" />
                            <SkeletonBlock class="mt-3 h-5 w-1/3 max-w-xs rounded" />
                            <SkeletonBlock class="mt-3 h-6 w-24 rounded-full" />
                        </div>
                        <div class="bg-surface ring-app rounded-2xl p-4 ring-1 lg:p-6">
                            <div class="space-y-2">
                                <SkeletonBlock class="h-4 w-full" />
                                <SkeletonBlock class="h-4 w-full" />
                                <SkeletonBlock class="h-4 w-5/6" />
                            </div>
                        </div>
                    </div>

                    <aside class="mt-8 hidden space-y-4 lg:sticky lg:top-6 lg:mt-0 lg:block">
                        <SkeletonBlock class="h-40 rounded-2xl" />
                        <SkeletonBlock class="h-48 rounded-2xl" />
                    </aside>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import SkeletonBlock from '@/components/skeleton/SkeletonBlock.vue';
</script>
