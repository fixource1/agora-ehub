<template>
    <RouterView />
    <AppToast />
    <ResourceQuickActionsMenu />
</template>

<script setup>
import { onMounted } from 'vue';
import AppToast from '@/components/ui/AppToast.vue';
import ResourceQuickActionsMenu from '@/components/resources/ResourceQuickActionsMenu.vue';
import { useLibrary } from '@/composables/useLibrary';

const library = useLibrary();

onMounted(() => {
    library.syncDownloads();
});
</script>
