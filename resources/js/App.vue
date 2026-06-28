<template>
    <div class="app-router-root flex min-h-0 flex-1 flex-col overflow-hidden">
        <RouterView v-slot="{ Component, route }">
            <KeepAlive v-if="route.meta.keepAlive">
                <component :is="Component" :key="route.name" />
            </KeepAlive>
            <component :is="Component" v-else :key="route.fullPath" />
        </RouterView>
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
