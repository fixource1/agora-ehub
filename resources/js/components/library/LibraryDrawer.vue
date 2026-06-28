<template>
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="library.state.drawerOpen"
                class="fixed inset-0 z-40 bg-black/40 tablet-sidebar:hidden"
                @click="library.closeDrawer()"
            />
        </Transition>

        <Transition name="slide">
            <aside
                v-if="library.state.drawerOpen"
                class="bg-sidebar fixed inset-y-0 left-0 z-50 flex w-[min(88vw,320px)] flex-col overflow-hidden shadow-2xl tablet-sidebar:hidden"
            >
                <LibrarySidebar embedded />
            </aside>
        </Transition>
    </Teleport>
</template>

<script setup>
import { onBeforeUnmount, onMounted } from 'vue';
import { useLibrary } from '@/composables/useLibrary';
import LibrarySidebar from '@/components/library/LibrarySidebar.vue';

const library = useLibrary();

onMounted(() => {
    window.addEventListener('orientationchange', library.closeDrawer);
});

onBeforeUnmount(() => {
    window.removeEventListener('orientationchange', library.closeDrawer);
});
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.slide-enter-active, .slide-leave-active { transition: transform 0.25s ease; }
.slide-enter-from, .slide-leave-to { transform: translateX(-100%); }
</style>
