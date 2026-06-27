<template>
    <dl class="grid grid-cols-3 gap-3 md:gap-4">
        <div
            v-for="index in 3"
            :key="index"
            class="bg-surface ring-app rounded-2xl px-3 py-3 ring-1 md:px-4 md:py-4"
        >
            <SkeletonBlock class="h-3 w-12" />
            <SkeletonBlock class="mt-2 h-6 w-8 md:h-7" />
        </div>
    </dl>
</template>

<script setup>
import SkeletonBlock from '@/components/skeleton/SkeletonBlock.vue';
</script>
