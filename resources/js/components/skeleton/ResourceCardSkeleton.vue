<template>
    <div class="border-app overflow-hidden rounded-xl border">
        <SkeletonBlock class="aspect-[3/4] w-full rounded-none" />
        <div class="space-y-2 p-2.5">
            <SkeletonBlock class="h-3 w-full" />
            <SkeletonBlock class="h-2 w-2/3" />
        </div>
    </div>
</template>

<script setup>
import SkeletonBlock from '@/components/skeleton/SkeletonBlock.vue';
</script>
