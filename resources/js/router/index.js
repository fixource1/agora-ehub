import { createRouter, createWebHistory } from 'vue-router';
import HomePage from '@/pages/HomePage.vue';
import LibraryPage from '@/pages/LibraryPage.vue';
import DiscoverPage from '@/pages/DiscoverPage.vue';
import ProfilePage from '@/pages/ProfilePage.vue';
import ResourceDetailPage from '@/pages/ResourceDetailPage.vue';

const routes = [
    { path: '/', redirect: '/home' },
    { path: '/home', name: 'home', component: HomePage },
    { path: '/library', name: 'library', component: LibraryPage },
    { path: '/discover', name: 'discover', component: DiscoverPage },
    { path: '/profile', name: 'profile', component: ProfilePage },
    { path: '/resources/:slug', name: 'resource.show', component: ResourceDetailPage },
];

export default createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior: () => ({ top: 0 }),
});
