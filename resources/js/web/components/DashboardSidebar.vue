<template>
    <aside
        class="web-sidebar bg-sidebar border-app flex h-screen shrink-0 flex-col border-r transition-[width] duration-200 ease-out"
        :class="collapsed ? 'web-sidebar--collapsed w-[4.75rem]' : 'w-64 xl:w-72'"
    >
        <div class="border-app shrink-0 border-b" :class="collapsed ? 'px-2 py-3' : 'px-4 py-4'">
            <div v-if="collapsed" class="flex flex-col items-center gap-2">
                <button
                    type="button"
                    class="text-muted hover:text-app flex h-8 w-8 items-center justify-center rounded-lg transition hover:bg-surface"
                    title="Expand sidebar"
                    aria-label="Expand sidebar"
                    @click="toggle()"
                >
                    <IconChevronLeft class="h-5 w-5 rotate-180" />
                </button>
                <BrandLogo size="sm" />
            </div>
            <div v-else class="flex items-center gap-3">
                <BrandLogo size="md" />
                <div class="min-w-0 flex-1">
                    <p class="text-app truncate text-sm font-semibold">{{ APP_NAME }}</p>
                    <p class="text-muted text-xs">Author Portal</p>
                </div>
                <button
                    type="button"
                    class="text-muted hover:text-app flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition hover:bg-surface"
                    title="Collapse sidebar"
                    aria-label="Collapse sidebar"
                    @click="toggle()"
                >
                    <IconChevronLeft class="h-5 w-5" />
                </button>
            </div>

            <div
                class="bg-surface ring-app flex items-center rounded-xl ring-1"
                :class="collapsed ? 'mt-3 justify-center p-2' : 'mt-4 gap-3 p-3'"
                :title="collapsed ? auth.user?.name : undefined"
            >
                <div class="bg-brand flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-xs font-semibold text-white">
                    {{ initials }}
                </div>
                <div v-show="!collapsed" class="min-w-0">
                    <p class="text-app truncate text-sm font-medium">{{ auth.user?.name }}</p>
                    <p class="text-muted truncate text-xs">{{ roleLabel }}</p>
                </div>
            </div>
        </div>

        <nav class="min-h-0 flex-1 overflow-x-hidden overflow-y-auto px-2 py-3" :class="collapsed ? 'px-2' : 'px-3'">
            <p v-show="!collapsed" class="web-nav-section">Author Dashboard</p>

            <RouterLink
                v-for="item in mainNav"
                :key="item.id"
                :to="item.to"
                active-class=""
                exact-active-class=""
                class="web-nav-link"
                :class="{ 'web-nav-link--active': activeNavKey === item.id, 'web-nav-link--collapsed': collapsed }"
                :title="collapsed ? item.label : undefined"
            >
                <component :is="item.icon" class="web-nav-link-icon h-5 w-5 shrink-0" />
                <span v-show="!collapsed" class="truncate">{{ item.label }}</span>
            </RouterLink>

            <p v-show="!collapsed" class="web-nav-section mt-5">Create</p>

            <RouterLink
                to="/author/resources/new"
                active-class=""
                exact-active-class=""
                class="web-nav-link"
                :class="{ 'web-nav-link--active': activeNavKey === 'new', 'web-nav-link--collapsed': collapsed }"
                :title="collapsed ? 'Add New Resource' : undefined"
            >
                <IconPlus class="web-nav-link-icon h-5 w-5 shrink-0" />
                <span v-show="!collapsed" class="truncate">Add New Resource</span>
            </RouterLink>

            <RouterLink
                to="/author/resources?status=draft"
                active-class=""
                exact-active-class=""
                class="web-nav-link"
                :class="{ 'web-nav-link--active': activeNavKey === 'drafts', 'web-nav-link--collapsed': collapsed }"
                :title="collapsed ? 'Drafts' : undefined"
            >
                <IconDocument class="web-nav-link-icon h-5 w-5 shrink-0" />
                <span v-show="!collapsed" class="truncate">Drafts</span>
            </RouterLink>

            <template v-if="adminNav.length">
                <p v-show="!collapsed" class="web-nav-section mt-5">Administration</p>

                <RouterLink
                    v-for="item in adminNav"
                    :key="item.id"
                    :to="item.to"
                    active-class=""
                    exact-active-class=""
                    class="web-nav-link"
                    :class="{ 'web-nav-link--active': activeNavKey === item.id, 'web-nav-link--collapsed': collapsed }"
                    :title="collapsed ? item.label : undefined"
                >
                    <component :is="item.icon" class="web-nav-link-icon h-5 w-5 shrink-0" />
                    <span v-show="!collapsed" class="truncate">{{ item.label }}</span>
                </RouterLink>
            </template>
        </nav>

        <div class="border-app shrink-0 border-t p-2" :class="collapsed ? 'px-2' : 'p-3'">
            <WebThemeToggle :collapsed="collapsed" />
            <a
                href="/"
                target="_blank"
                rel="noopener noreferrer"
                class="web-utility-link"
                :class="{ 'web-utility-link--collapsed': collapsed }"
                :title="collapsed ? 'Preview App' : undefined"
            >
                <IconExternalLink class="h-5 w-5 shrink-0" />
                <span v-show="!collapsed">Preview App</span>
            </a>
            <button
                type="button"
                class="web-utility-link web-utility-link--muted"
                :class="{ 'web-utility-link--collapsed': collapsed }"
                :title="collapsed ? 'Sign out' : undefined"
                @click="signOut"
            >
                <IconLogout class="h-5 w-5 shrink-0" />
                <span v-show="!collapsed">Sign out</span>
            </button>
        </div>
    </aside>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import BrandLogo from '@/components/brand/BrandLogo.vue';
import { APP_NAME } from '@/constants/brand';
import IconBooks from '@/components/icons/IconBooks.vue';
import IconChartBar from '@/components/icons/IconChartBar.vue';
import IconChevronLeft from '@/components/icons/IconChevronLeft.vue';
import IconDocument from '@/components/icons/IconDocument.vue';
import IconExternalLink from '@/components/icons/IconExternalLink.vue';
import IconLogout from '@/components/icons/IconLogout.vue';
import IconPlus from '@/components/icons/IconPlus.vue';
import IconUser from '@/components/icons/IconUser.vue';
import IconLibrary from '@/components/icons/IconLibrary.vue';
import { useAuthStore } from '../stores/auth';
import { useAuthorSidebar } from '../composables/useAuthorSidebar';
import { useAdminAccess } from '../composables/useAdminAccess';
import WebThemeToggle from './WebThemeToggle.vue';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();
const { collapsed, toggle } = useAuthorSidebar();
const { canManageUsers, canManageCategories, canManageResources } = useAdminAccess();

const mainNav = computed(() => [
    { id: 'dashboard', to: '/author', label: 'Dashboard', icon: IconChartBar },
    {
        id: 'resources',
        to: '/author/resources',
        label: canManageResources.value ? 'All Resources' : 'My Resources',
        icon: IconBooks,
    },
]);

const adminNav = computed(() => {
    const items = [];

    if (canManageUsers.value) {
        items.push({ id: 'users', to: '/author/admin/users', label: 'Users', icon: IconUser });
    }

    if (canManageCategories.value) {
        items.push({ id: 'categories', to: '/author/admin/categories', label: 'Categories', icon: IconLibrary });
    }

    return items;
});

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

const activeNavKey = computed(() => {
    const { path, query } = route;

    if (path === '/author/resources/new') {
        return 'new';
    }

    if (path === '/author/resources' && query.status === 'draft') {
        return 'drafts';
    }

    if (path === '/author/resources' || (path.startsWith('/author/resources/') && !path.startsWith('/author/resources/new'))) {
        return 'resources';
    }

    if (path.startsWith('/author/admin/users')) {
        return 'users';
    }

    if (path.startsWith('/author/admin/categories')) {
        return 'categories';
    }

    if (path === '/author') {
        return 'dashboard';
    }

    return null;
});

async function signOut() {
    await auth.logout();
    router.push({ name: 'web.login' });
}
</script>
