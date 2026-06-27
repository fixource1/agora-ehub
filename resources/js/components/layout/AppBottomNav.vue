<template>
    <nav class="app-bottom-nav fixed inset-x-0 bottom-0 z-30">
        <div class="app-bottom-nav-bar flex items-center justify-center">
            <div class="app-bottom-nav-menu flex w-full max-w-[22rem] items-stretch gap-0.5 sm:max-w-[23rem]">
                <AppBottomNavItem
                    v-for="item in items"
                    :key="item.name"
                    :to="item.to"
                    :label="item.label"
                    :icon="item.icon"
                    :active="$route.name === item.name"
                />
            </div>
        </div>
    </nav>
</template>

<script setup>
import AppBottomNavItem from '@/components/layout/AppBottomNavItem.vue';
import IconHome from '@/components/icons/IconHome.vue';
import IconLibrary from '@/components/icons/IconLibrary.vue';
import IconDiscover from '@/components/icons/IconDiscover.vue';
import IconUser from '@/components/icons/IconUser.vue';

const items = [
    { name: 'home', to: '/home', label: 'Home', icon: IconHome },
    { name: 'library', to: '/library', label: 'Library', icon: IconLibrary },
    { name: 'discover', to: '/discover', label: 'Discover', icon: IconDiscover },
    { name: 'profile', to: '/profile', label: 'Profile', icon: IconUser },
];
</script>
