<template>
    <div class="app-shell bg-app text-app min-h-[100dvh]">
        <slot />
        <AppBottomNav v-if="showBottomNav" />
    </div>
</template>

<script setup>
import AppBottomNav from '@/components/layout/AppBottomNav.vue';

defineProps({
    showBottomNav: { type: Boolean, default: true },
});
</script>
