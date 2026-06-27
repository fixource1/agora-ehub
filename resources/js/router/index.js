import { createRouter, createWebHashHistory } from 'vue-router';

/**
 * Hash history keeps in-app navigation inside the WebView. NativePHP's Android
 * WebView reloads the whole Laravel shell for path-based URLs like /resources/foo.
 */

const routes = [
    { path: '/', redirect: '/home' },
    { path: '/home', name: 'home', component: () => import('@/pages/HomePage.vue') },
    { path: '/library', name: 'library', component: () => import('@/pages/LibraryPage.vue') },
    { path: '/discover', name: 'discover', component: () => import('@/pages/DiscoverPage.vue') },
    { path: '/profile', name: 'profile', component: () => import('@/pages/ProfilePage.vue') },
    { path: '/resources/:slug', name: 'resource.show', component: () => import('@/pages/ResourceDetailPage.vue') },
];

export default createRouter({
    history: createWebHashHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        }

        if (to.name === 'resource.show' && from.name && from.name !== 'resource.show') {
            return { top: 0 };
        }

        return { top: 0 };
    },
});
