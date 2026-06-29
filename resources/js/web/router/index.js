import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useAdminAccess } from '../composables/useAdminAccess';

const routes = [
    {
        path: '/author/login',
        name: 'web.login',
        component: () => import('../pages/LoginPage.vue'),
        meta: { guest: true },
    },
    {
        path: '/author',
        component: () => import('../layouts/DashboardLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'web.dashboard',
                component: () => import('../pages/DashboardPage.vue'),
            },
            {
                path: 'resources',
                name: 'web.resources',
                component: () => import('../pages/MyResourcesPage.vue'),
            },
            {
                path: 'resources/new',
                name: 'web.resources.create',
                component: () => import('../pages/ResourceFormPage.vue'),
            },
            {
                path: 'resources/:slug/edit',
                name: 'web.resources.edit',
                component: () => import('../pages/ResourceFormPage.vue'),
                props: true,
            },
            {
                path: 'admin/users',
                name: 'web.admin.users',
                component: () => import('../pages/UsersPage.vue'),
                meta: { requiresPermission: 'can_manage_users' },
            },
            {
                path: 'admin/users/new',
                name: 'web.admin.users.create',
                component: () => import('../pages/UserFormPage.vue'),
                meta: { requiresPermission: 'can_manage_users' },
            },
            {
                path: 'admin/users/:id/edit',
                name: 'web.admin.users.edit',
                component: () => import('../pages/UserFormPage.vue'),
                props: true,
                meta: { requiresPermission: 'can_manage_users' },
            },
            {
                path: 'admin/categories',
                name: 'web.admin.categories',
                component: () => import('../pages/CategoriesPage.vue'),
                meta: { requiresPermission: 'can_manage_categories' },
            },
            {
                path: 'admin/categories/new',
                name: 'web.admin.categories.create',
                component: () => import('../pages/CategoryFormPage.vue'),
                meta: { requiresPermission: 'can_manage_categories' },
            },
            {
                path: 'admin/categories/:id/edit',
                name: 'web.admin.categories.edit',
                component: () => import('../pages/CategoryFormPage.vue'),
                props: true,
                meta: { requiresPermission: 'can_manage_categories' },
            },
        ],
    },
    {
        path: '/author/:pathMatch(.*)*',
        redirect: '/author',
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();

    if (!auth.initialized) {
        await auth.initialize();
    }

    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return { name: 'web.login', query: { redirect: to.fullPath } };
    }

    if (to.meta.guest && auth.isAuthenticated) {
        return { name: 'web.dashboard' };
    }

    if (to.meta.requiresPermission) {
        const { canManageUsers, canManageCategories } = useAdminAccess();
        const permission = to.meta.requiresPermission;

        if (permission === 'can_manage_users' && !canManageUsers.value) {
            return { name: 'web.dashboard' };
        }

        if (permission === 'can_manage_categories' && !canManageCategories.value) {
            return { name: 'web.dashboard' };
        }
    }
});

export default router;
