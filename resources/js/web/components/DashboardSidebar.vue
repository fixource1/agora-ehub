<template>
    <aside class="web-sidebar bg-sidebar border-app flex h-screen w-72 shrink-0 flex-col border-r">
        <div class="border-app shrink-0 border-b px-5 py-5">
            <div class="flex items-center gap-3">
                <div class="bg-brand flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-sm font-semibold text-white">
                    {{ initials }}
                </div>
                <div class="min-w-0">
                    <p class="text-app truncate text-sm font-semibold">{{ auth.user?.name }}</p>
                    <p class="text-muted truncate text-xs">{{ roleLabel }}</p>
                </div>
            </div>
        </div>

        <nav class="min-h-0 flex-1 overflow-y-auto px-3 py-4">
            <p class="text-muted px-3 pb-2 text-[11px] font-semibold uppercase tracking-wide">Author Dashboard</p>
            <RouterLink
                v-for="item in mainNav"
                :key="item.to"
                :to="item.to"
                class="web-nav-link mb-1 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm"
                :class="{ 'router-link-active': isNavActive(item) }"
            >
                <span class="text-base" aria-hidden="true">{{ item.icon }}</span>
                {{ item.label }}
            </RouterLink>

            <p class="text-muted mt-6 px-3 pb-2 text-[11px] font-semibold uppercase tracking-wide">Create</p>
            <RouterLink
                to="/author/resources/new"
                class="web-nav-link mb-1 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm"
                active-class="router-link-active"
            >
                <span class="text-base" aria-hidden="true">+</span>
                Add New Resource
            </RouterLink>
            <RouterLink
                to="/author/resources?status=draft"
                class="web-nav-link mb-1 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm"
                :class="{ 'router-link-active': isDraftsActive }"
            >
                <span class="text-base" aria-hidden="true">📝</span>
                Drafts
            </RouterLink>
        </nav>

        <div class="border-app shrink-0 space-y-2 border-t p-4">
            <WebThemeToggle />
            <a
                href="/"
                target="_blank"
                class="text-brand border-app flex w-full items-center justify-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-medium transition hover:bg-surface-muted"
            >
                Preview App
                <span aria-hidden="true">↗</span>
            </a>
            <button
                type="button"
                class="text-muted w-full rounded-lg px-4 py-2 text-sm transition hover:bg-surface-muted hover:text-app"
                @click="signOut"
            >
                Sign out
            </button>
        </div>
    </aside>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import WebThemeToggle from './WebThemeToggle.vue';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const mainNav = [
    { to: '/author', label: 'Dashboard', icon: '📊', exact: true },
    { to: '/author/resources', label: 'My Resources', icon: '📚', exact: false },
];

const initials = computed(() => {
    const parts = (auth.user?.name ?? 'U').split(' ');
    return parts.slice(0, 2).map((p) => p[0]).join('').toUpperCase();
});

const roleLabel = computed(() => {
    if (auth.user?.role === 'administrator') {
        return 'Administrator';
    }
    return 'Author';
});

const isDraftsActive = computed(() =>
    route.path === '/author/resources' && route.query.status === 'draft',
);

function isNavActive(item) {
    if (item.exact) {
        return route.path === item.to;
    }

    if (item.to === '/author/resources') {
        return route.path.startsWith('/author/resources') && route.query.status !== 'draft';
    }

    return route.path.startsWith(item.to);
}

async function signOut() {
    await auth.logout();
    router.push({ name: 'web.login' });
}
</script>
