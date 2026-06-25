<template>
    <button
        type="button"
        class="web-theme-toggle"
        :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        @click="toggle()"
    >
        <span v-if="isDark" aria-hidden="true">☀️</span>
        <span v-else aria-hidden="true">🌙</span>
        <span>{{ isDark ? 'Light mode' : 'Dark mode' }}</span>
    </button>
</template>

<script setup>
import { useTheme } from '@/composables/useTheme';

const { isDark, toggle } = useTheme();
</script>
