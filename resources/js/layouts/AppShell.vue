<template>
    <div class="app-shell bg-app text-app flex min-h-0 flex-1 flex-col overflow-hidden">
        <main class="app-shell__main flex min-h-0 flex-1 flex-col overflow-hidden">
            <slot />
        </main>
        <AppBottomNav v-if="showBottomNav" />
    </div>
</template>

<script setup>
import AppBottomNav from '@/components/layout/AppBottomNav.vue';

defineProps({
    showBottomNav: { type: Boolean, default: true },
});
</script>
