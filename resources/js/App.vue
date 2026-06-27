<template>
    <div class="app-router-root flex min-h-0 flex-1 flex-col overflow-hidden">
        <RouterView />
        <AppToast />
        <ResourceQuickActionsMenu />
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import AppToast from '@/components/ui/AppToast.vue';
import ResourceQuickActionsMenu from '@/components/resources/ResourceQuickActionsMenu.vue';
import { useLibrary } from '@/composables/useLibrary';

const library = useLibrary();

onMounted(() => {
    library.hydrateDownloads().catch(() => {});
    library.syncDownloads();
});
</script>
