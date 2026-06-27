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

            <div class="bg-surface ring-app mt-4 rounded-2xl p-4 ring-1">
                <p class="text-app mb-2 font-medium">Data source</p>
                <p
                    class="text-sm font-medium"
                    :class="isRemoteApi ? 'text-brand' : 'text-muted'"
                >
                    {{ dataSourceLabel }}
                </p>
                <p class="text-muted mt-2 font-mono text-xs break-all">
                    {{ apiBaseUrl }}
                </p>
                <p class="text-muted mt-3 text-xs leading-relaxed">
                    {{ dataSourceHint }}
                </p>
                <button
                    type="button"
                    class="bg-surface-muted text-app mt-4 w-full rounded-xl px-4 py-3 text-sm font-medium"
                    :disabled="apiTesting"
                    @click="testApiConnection"
                >
                    {{ apiTesting ? 'Testing…' : 'Test API connection' }}
                </button>
                <p
                    v-if="apiTestResult"
                    class="mt-3 text-sm"
                    :class="apiTestOk ? 'text-brand' : 'text-red-600'"
                >
                    {{ apiTestResult }}
                </p>
            </div>

            <div class="bg-surface ring-app mt-4 rounded-2xl p-4 ring-1">
                <p class="text-app mb-3 font-medium">Resource layout</p>
                <p class="text-muted mb-3 text-xs">Applies to Library and Discover</p>
                <ResourceViewToggle />
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
import { computed, ref } from 'vue';
import AppShell from '@/layouts/AppShell.vue';
import MobileTopBar from '@/components/layout/MobileTopBar.vue';
import { useTheme } from '@/composables/useTheme';
import { useNative } from '@/composables/useNative';
import ResourceViewToggle from '@/components/resources/ResourceViewToggle.vue';

const { isDark, toggle } = useTheme();
const { isNative, isRemoteApi, isEmbeddedApi, apiBaseUrl } = useNative();

const dataSourceLabel = computed(() => {
    if (isRemoteApi) {
        return 'Remote API (WSL / Docker)';
    }

    if (isEmbeddedApi) {
        return 'Embedded API (on-device SQLite)';
    }

    return 'Local API (this Laravel server)';
});

const dataSourceHint = computed(() => {
    if (isRemoteApi) {
        return 'Calls leave the device to your host machine. Stopping Docker should break the test below.';
    }

    if (isEmbeddedApi) {
        return 'Calls stay on this device (127.0.0.1). Stopping Docker on WSL has no effect.';
    }

    return 'Web app uses the same server that serves this page.';
});

const apiTesting = ref(false);
const apiTestResult = ref('');
const apiTestOk = ref(false);

async function testApiConnection() {
    apiTesting.value = true;
    apiTestResult.value = '';

    try {
        const { data } = await window.axios.get('/resources');
        const count = data.data?.length ?? 0;
        apiTestOk.value = true;
        apiTestResult.value = `Connected — ${count} resource(s) returned from ${apiBaseUrl}`;
    } catch {
        apiTestOk.value = false;
        apiTestResult.value = isRemoteApi
            ? `Failed — could not reach ${apiBaseUrl}. On WSL2, run scripts\\setup-android-api-access.cmd on Windows, bump NATIVEPHP_APP_VERSION_CODE, and rebuild.`
            : `Failed — could not reach ${apiBaseUrl}.`;
    } finally {
        apiTesting.value = false;
    }
}

const menuItems = ['Reading Preferences', 'Notifications', 'Help & Support', 'Sign Out'];
</script>
