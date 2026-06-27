<template>
    <aside class="library-sidebar bg-sidebar border-app flex h-full w-full flex-col overflow-hidden border-r">
        <div
            class="border-app shrink-0 border-b"
            :class="embedded ? 'safe-top px-4 pb-3 pt-3' : 'safe-top px-5 pb-4 pt-4'"
        >
            <div class="flex items-center gap-3">
                <BrandLogo size="md" />
                <div class="min-w-0">
                    <p class="font-display text-app truncate text-lg font-semibold">My Library</p>
                    <p class="text-muted text-xs">{{ APP_NAME }}</p>
                </div>
            </div>
        </div>

        <nav
            class="flex min-h-0 flex-1 flex-col overflow-hidden px-3"
            :class="embedded ? 'py-3' : 'overflow-y-auto py-4'"
        >
            <button
                v-for="item in navItems"
                :key="item.id"
                class="library-nav-item flex w-full shrink-0 items-center justify-between rounded-xl px-3 text-left text-sm transition"
                :class="[
                    embedded ? 'mb-0.5 py-2' : 'mb-1 py-3',
                    library.state.activeSection === item.id
                        ? 'bg-brand text-white shadow-sm font-medium'
                        : 'text-app hover:bg-surface-muted',
                ]"
                @click="selectSection(item.id)"
            >
                <span class="flex min-w-0 items-center gap-3">
                    <component :is="item.icon" class="library-nav-item-icon h-5 w-5 shrink-0" />
                    <span class="truncate">{{ item.label }}</span>
                </span>
                <span
                    v-if="item.count != null"
                    class="library-nav-item-count ml-2 shrink-0 text-xs"
                    :class="library.state.activeSection === item.id ? 'text-white/90' : 'text-muted'"
                >
                    {{ item.count }}
                </span>
            </button>

            <div class="mt-3 shrink-0 px-1" :class="embedded ? '' : 'mt-6 px-3'">
                <div class="mb-2 flex items-center justify-between gap-2">
                    <p class="text-muted text-xs font-semibold uppercase tracking-wide">Collections</p>
                    <button
                        type="button"
                        class="library-collections-add"
                        aria-label="Add collection"
                    >
                        <IconPlus class="h-3.5 w-3.5" />
                    </button>
                </div>
                <button
                    v-for="collection in visibleCollections"
                    :key="collection.id"
                    class="text-app hover:bg-surface-muted flex w-full items-center justify-between rounded-lg px-2 text-sm"
                    :class="embedded ? 'py-1.5' : 'mb-1 py-2'"
                >
                    <span class="truncate">{{ collection.name }}</span>
                    <span class="text-muted ml-2 shrink-0 text-xs">{{ collection.count }}</span>
                </button>
            </div>
        </nav>
    </aside>
</template>

<script setup>
import { computed } from 'vue';
import { useLibrary } from '@/composables/useLibrary';
import IconBooks from '@/components/icons/IconBooks.vue';
import BrandLogo from '@/components/brand/BrandLogo.vue';
import { APP_NAME } from '@/constants/brand';
import IconDownload from '@/components/icons/IconDownload.vue';
import IconBookmark from '@/components/icons/IconBookmark.vue';
import IconNotes from '@/components/icons/IconNotes.vue';
import IconClock from '@/components/icons/IconClock.vue';
import IconPlus from '@/components/icons/IconPlus.vue';

const props = defineProps({
    embedded: { type: Boolean, default: false },
});

const library = useLibrary();

const navItems = [
    { id: 'offline', label: 'Offline', count: library.state.counts.offline, icon: IconDownload },
    { id: 'all', label: 'All Resources', count: library.state.counts.allResources, icon: IconBooks },
    { id: 'notes', label: 'Notes', count: library.state.counts.notes, icon: IconNotes },
    { id: 'bookmarks', label: 'Bookmarks', count: library.state.counts.bookmarks, icon: IconBookmark },
    { id: 'recent', label: 'Recently Opened', count: library.state.recentSlugs.length, icon: IconClock },
];

const visibleCollections = computed(() =>
    props.embedded
        ? library.state.collections.slice(0, 3)
        : library.state.collections,
);

function selectSection(id) {
    library.setActiveSection(id);
    library.closeDrawer();
}
</script>
