<template>
    <nav class="app-bottom-nav fixed inset-x-0 bottom-0 z-30">
        <div class="app-bottom-nav-bar flex items-center justify-center">
            <div class="app-bottom-nav-menu flex w-full max-w-[22rem] items-stretch gap-0.5 sm:max-w-[23rem]">
                <RouterLink
                    v-for="item in items"
                    :key="item.name"
                    :to="item.to"
                    class="app-bottom-nav-item flex flex-1 flex-col items-center justify-center gap-0.5 px-1 py-2 text-[10px] font-medium sm:text-[11px]"
                    :class="$route.name === item.name ? 'app-bottom-nav-item--active text-brand' : 'text-muted'"
                >
                    <component
                        :is="item.icon"
                        :filled="$route.name === item.name"
                        class="h-5 w-5 shrink-0 sm:h-[1.375rem] sm:w-[1.375rem]"
                    />
                    <span class="leading-none">{{ item.label }}</span>
                </RouterLink>
            </div>
        </div>
    </nav>
</template>

<script setup>
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
