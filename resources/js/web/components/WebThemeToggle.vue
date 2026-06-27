<template>
    <button
        type="button"
        class="web-theme-toggle"
        :class="{ 'web-theme-toggle--collapsed': collapsed }"
        :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        :title="collapsed ? (isDark ? 'Light mode' : 'Dark mode') : undefined"
        @click="toggle()"
    >
        <IconSun v-if="isDark" class="h-5 w-5 shrink-0" />
        <IconMoon v-else class="h-5 w-5 shrink-0" />
        <span v-show="!collapsed">{{ isDark ? 'Light mode' : 'Dark mode' }}</span>
    </button>
</template>

<script setup>
import IconMoon from '@/components/icons/IconMoon.vue';
import IconSun from '@/components/icons/IconSun.vue';
import { useTheme } from '@/composables/useTheme';

defineProps({
    collapsed: { type: Boolean, default: false },
});

const { isDark, toggle } = useTheme();
</script>
