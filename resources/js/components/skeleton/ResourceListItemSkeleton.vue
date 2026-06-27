<template>
    <div class="flex items-stretch gap-3 overflow-hidden rounded-xl p-2.5 ring-1 ring-app sm:gap-4 sm:p-3">
        <SkeletonBlock class="aspect-[3/4] w-14 shrink-0 rounded-md sm:w-16" />
        <div class="flex min-w-0 flex-1 flex-col justify-center gap-2 py-0.5">
            <SkeletonBlock class="h-4 w-full" />
            <SkeletonBlock class="h-3 w-2/3" />
        </div>
    </div>
</template>

<script setup>
import SkeletonBlock from '@/components/skeleton/SkeletonBlock.vue';
</script>
