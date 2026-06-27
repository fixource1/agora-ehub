import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    { path: '/', redirect: '/home' },
    { path: '/home', name: 'home', component: () => import('@/pages/HomePage.vue') },
    { path: '/library', name: 'library', component: () => import('@/pages/LibraryPage.vue') },
    { path: '/discover', name: 'discover', component: () => import('@/pages/DiscoverPage.vue') },
    { path: '/profile', name: 'profile', component: () => import('@/pages/ProfilePage.vue') },
    { path: '/resources/:slug', name: 'resource.show', component: () => import('@/pages/ResourceDetailPage.vue') },
];

export default createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior: () => ({ top: 0 }),
});
