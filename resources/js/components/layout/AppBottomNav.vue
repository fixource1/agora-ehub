<template>
    <nav class="app-bottom-nav bg-surface/95 border-app safe-bottom fixed inset-x-0 bottom-0 z-30 border-t backdrop-blur">
        <div class="mx-auto grid max-w-lg grid-cols-4 px-2 pt-2 pb-1 md:max-w-3xl lg:max-w-5xl xl:max-w-6xl">
            <RouterLink
                v-for="item in items"
                :key="item.name"
                :to="item.to"
                class="text-muted flex flex-col items-center gap-1 rounded-xl px-2 py-2 text-[11px] font-medium md:py-2.5 md:text-xs"
            >
                <component
                    :is="item.icon"
                    class="h-6 w-6 md:h-7 md:w-7"
                    :class="$route.name === item.name ? 'text-brand' : 'text-muted'"
                />
                <span :class="$route.name === item.name ? 'text-brand font-medium' : ''">{{ item.label }}</span>
                <span
                    v-if="$route.name === item.name"
                    class="bg-brand h-0.5 w-8 rounded-full md:w-10"
                />
            </RouterLink>
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
