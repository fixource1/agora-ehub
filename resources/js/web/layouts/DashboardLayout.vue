<template>
    <div class="web-dashboard bg-app flex h-screen overflow-hidden">
        <DashboardSidebar />

        <div class="flex min-h-0 min-w-0 flex-1 flex-col">
            <main class="min-h-0 flex-1 overflow-y-auto">
                <RouterView />
            </main>
        </div>
    </div>
</template>

<script setup>
import DashboardSidebar from '../components/DashboardSidebar.vue';
</script>
