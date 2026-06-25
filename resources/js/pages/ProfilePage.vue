<template>
    <AppShell>
        <MobileTopBar title="Profile" />
        <div class="page-content mx-auto max-w-lg px-4 pt-6 md:max-w-2xl md:pt-8">
            <div class="bg-surface ring-app flex flex-col items-center rounded-2xl p-6 ring-1 md:p-8">
                <div class="bg-brand-subtle text-brand flex h-20 w-20 items-center justify-center rounded-full text-2xl font-bold">
                    DA
                </div>
                <p class="text-app mt-4 text-lg font-semibold">Domingo Abucay</p>
                <p class="text-brand text-sm">Reader</p>
                <p class="text-muted mt-2 text-sm">UPLB OVCRE</p>
            </div>

            <div class="bg-surface ring-app mt-6 rounded-2xl p-4 ring-1">
                <div class="flex items-center justify-between">
                    <p class="text-app font-medium">Dark mode</p>
                    <button
                        class="relative h-8 w-14 rounded-full transition"
                        :class="isDark ? 'bg-brand' : 'bg-surface-muted'"
                        @click="toggle()"
                    >
                        <span
                            class="absolute top-1 h-6 w-6 rounded-full bg-white shadow transition"
                            :class="isDark ? 'left-7' : 'left-1'"
                        />
                    </button>
                </div>
            </div>

            <div class="mt-6 space-y-2">
                <button
                    v-for="item in menuItems"
                    :key="item"
                    class="bg-surface ring-app text-app flex w-full items-center justify-between rounded-xl px-4 py-4 text-sm ring-1"
                >
                    {{ item }}
                    <span class="text-muted">›</span>
                </button>
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import AppShell from '@/layouts/AppShell.vue';
import MobileTopBar from '@/components/layout/MobileTopBar.vue';
import { useTheme } from '@/composables/useTheme';

const { isDark, toggle } = useTheme();

const menuItems = ['Reading Preferences', 'Notifications', 'Help & Support', 'Sign Out'];
</script>
