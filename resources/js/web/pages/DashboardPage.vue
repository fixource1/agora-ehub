<template>
    <div class="px-8 py-8">
        <h1 class="text-app text-3xl font-bold">Dashboard</h1>
        <p class="text-muted mt-1">Welcome back, {{ auth.user?.name }}.</p>

        <div class="mt-8 grid gap-4 sm:grid-cols-3">
            <div class="web-card p-5">
                <p class="text-muted text-sm">Total resources</p>
                <p class="text-app mt-2 text-3xl font-bold">{{ stats.total }}</p>
            </div>
            <div class="web-card p-5">
                <p class="text-muted text-sm">Published</p>
                <p class="text-brand mt-2 text-3xl font-bold">{{ stats.published }}</p>
            </div>
            <div class="web-card p-5">
                <p class="text-muted text-sm">Drafts</p>
                <p class="text-app mt-2 text-3xl font-bold">{{ stats.drafts }}</p>
            </div>
        </div>

        <div class="mt-8 flex items-center justify-between">
            <h2 class="text-app text-lg font-semibold">Recent resources</h2>
            <RouterLink to="/author/resources/new" class="bg-brand rounded-lg px-4 py-2 text-sm font-medium text-white">
                Upload New Resource
            </RouterLink>
        </div>

        <div v-if="loading" class="text-muted mt-6 text-sm">Loading...</div>

        <div v-else-if="resources.length" class="web-card mt-4 overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="web-table-head">
                    <tr>
                        <th class="px-5 py-3 font-medium">Title</th>
                        <th class="px-5 py-3 font-medium">Type</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="resource in resources" :key="resource.id" class="web-table-row last:border-0">
                        <td class="text-app px-5 py-4 font-medium">{{ resource.title }}</td>
                        <td class="text-muted px-5 py-4">{{ resource.resource_type?.name }}</td>
                        <td class="px-5 py-4">
                            <span class="web-status-badge" :class="`web-status-${resource.status}`">
                                {{ formatStatus(resource.status) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <RouterLink
                                :to="{ name: 'web.resources.edit', params: { slug: resource.slug } }"
                                class="text-brand text-sm font-medium hover:underline"
                            >
                                Edit
                            </RouterLink>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p v-else class="text-muted mt-6 text-sm">No resources yet. Create your first one.</p>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { client } from '../api/client';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const resources = ref([]);
const loading = ref(true);

const stats = computed(() => ({
    total: resources.value.length,
    published: resources.value.filter((r) => r.status === 'published').length,
    drafts: resources.value.filter((r) => r.status === 'draft').length,
}));

function formatStatus(status) {
    return status?.replace('_', ' ') ?? status;
}

onMounted(async () => {
    try {
        const { data } = await client.get('/my/resources', { params: { per_page: 5 } });
        resources.value = data.data ?? [];
    } finally {
        loading.value = false;
    }
});
</script>
