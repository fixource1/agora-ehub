<template>
    <header class="bg-app/95 border-app safe-top z-30 border-b backdrop-blur">
        <div class="flex items-center gap-3 px-4 pb-3 pt-1">
            <BrandLogo
                v-if="showLogo && !showBack && !showMenu"
                size="md"
                class="shrink-0"
            />
            <PressableButton
                v-if="showBack"
                icon
                class="bg-surface ring-app flex h-10 w-10 items-center justify-center rounded-full ring-1"
                aria-label="Go back"
                @click="goBack"
            >
                <IconBack class="h-5 w-5" />
            </PressableButton>
            <PressableButton
                v-else-if="showMenu"
                icon
                class="bg-surface ring-app flex h-10 w-10 items-center justify-center rounded-full ring-1"
                aria-label="Open menu"
                @click="library.toggleDrawer()"
            >
                <IconMenu class="h-5 w-5" />
            </PressableButton>

            <div class="min-w-0 flex-1">
                <h1 v-if="title" class="text-app truncate text-lg font-semibold">{{ title }}</h1>
                <p v-if="subtitle" class="text-muted truncate text-xs">{{ subtitle }}</p>
            </div>

            <div class="flex items-center gap-2">
                <slot name="actions" />
                <PressableButton
                    v-if="showSearch"
                    icon
                    class="bg-surface ring-app flex h-10 w-10 items-center justify-center rounded-full ring-1"
                    aria-label="Search"
                    @click="$emit('search')"
                >
                    <IconSearch class="h-5 w-5" />
                </PressableButton>
            </div>
        </div>
    </header>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { useLibrary } from '@/composables/useLibrary';
import PressableButton from '@/components/ui/PressableButton.vue';
import IconBack from '@/components/icons/IconBack.vue';
import IconMenu from '@/components/icons/IconMenu.vue';
import IconSearch from '@/components/icons/IconSearch.vue';
import BrandLogo from '@/components/brand/BrandLogo.vue';

defineProps({
    title: String,
    subtitle: String,
    showBack: { type: Boolean, default: false },
    showMenu: { type: Boolean, default: false },
    showSearch: { type: Boolean, default: false },
    showLogo: { type: Boolean, default: false },
});

defineEmits(['search']);

const router = useRouter();
const library = useLibrary();

function goBack() {
    if (window.history.length > 1) {
        router.back();
    } else {
        router.push('/library');
    }
}
</script>
