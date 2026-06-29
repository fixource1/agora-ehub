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
import { onBeforeUnmount, onMounted } from 'vue';
import AppToast from '@/components/ui/AppToast.vue';
import ResourceQuickActionsMenu from '@/components/resources/ResourceQuickActionsMenu.vue';
import { hydrateAppStorage, reloadAppStorage } from '@/lib/appStorage';
import { useLibrary } from '@/composables/useLibrary';

const library = useLibrary();

async function refreshLibraryStorage() {
    await reloadAppStorage().catch(() => {});
    library.reloadFromStorage();
}

function onVisibilityChange() {
    if (document.visibilityState === 'visible') {
        refreshLibraryStorage().catch(() => {});
    }
}

onMounted(() => {
    library.hydrateDownloads().catch(() => {});
    library.syncDownloads();
    library.refreshReaderCounts();
    document.addEventListener('visibilitychange', onVisibilityChange);
});

onBeforeUnmount(() => {
    document.removeEventListener('visibilitychange', onVisibilityChange);
});
</script>
