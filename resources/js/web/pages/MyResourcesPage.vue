<template>
    <div class="px-8 py-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-app text-3xl font-bold">{{ pageTitle }}</h1>
                <p class="text-muted mt-1">{{ pageSubtitle }}</p>
            </div>
            <RouterLink to="/author/resources/new" class="bg-brand rounded-xl px-5 py-2.5 text-sm font-semibold text-white">
                + Add New Resource
            </RouterLink>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <input
                v-model="query"
                type="search"
                placeholder="Search resources..."
                class="web-input max-w-xs"
                @input="debouncedLoad"
            >
            <select v-model="statusFilter" class="web-input w-auto" @change="load">
                <option value="">All statuses</option>
                <option value="draft">Draft</option>
                <option value="pending_review">Pending review</option>
                <option value="published">Published</option>
                <option value="archived">Archived</option>
            </select>
        </div>

        <div v-if="loading" class="text-muted mt-10 text-center text-sm">Loading resources...</div>

        <div v-else-if="resources.length" class="web-card mt-6 overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="web-table-head">
                    <tr>
                        <th class="px-5 py-3 font-medium">Title</th>
                        <th v-if="canManageResources" class="px-5 py-3 font-medium">Uploader</th>
                        <th class="px-5 py-3 font-medium">Category</th>
                        <th class="px-5 py-3 font-medium">Type</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium">Updated</th>
                        <th class="px-5 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="resource in resources" :key="resource.id" class="web-table-row last:border-0">
                        <td class="px-5 py-4">
                            <p class="text-app font-medium">{{ resource.title }}</p>
                            <p v-if="resource.subtitle" class="text-muted text-xs">{{ resource.subtitle }}</p>
                        </td>
                        <td v-if="canManageResources" class="text-muted px-5 py-4">
                            {{ resource.uploader?.name ?? '—' }}
                        </td>
                        <td class="text-muted px-5 py-4">{{ resource.category?.name ?? '—' }}</td>
                        <td class="text-muted px-5 py-4">{{ resource.resource_type?.name }}</td>
                        <td class="px-5 py-4">
                            <span class="web-status-badge" :class="`web-status-${resource.status}`">
                                {{ formatStatus(resource.status) }}
                            </span>
                        </td>
                        <td class="text-muted px-5 py-4">{{ formatDate(resource.updated_at) }}</td>
                        <td class="px-5 py-4 text-right">
                            <RouterLink
                                :to="{ name: 'web.resources.edit', params: { slug: resource.slug } }"
                                class="text-brand mr-3 text-sm font-medium hover:underline"
                            >
                                Edit
                            </RouterLink>
                            <button
                                v-if="canDelete(resource)"
                                class="text-sm text-red-600 hover:underline dark:text-red-400"
                                @click="remove(resource)"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else class="web-card border-dashed mt-10 p-12 text-center">
            <p class="text-app font-medium">No resources found</p>
            <p class="text-muted mt-2 text-sm">Start by uploading your first resource.</p>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { client } from '../api/client';
import { useAdminAccess } from '../composables/useAdminAccess';

const route = useRoute();
const { canManageResources } = useAdminAccess();

const pageTitle = computed(() => (canManageResources.value ? 'All Resources' : 'My Resources'));
const pageSubtitle = computed(() => (
    canManageResources.value
        ? 'Manage all uploaded materials across authors'
        : 'Manage your uploaded materials'
));
const resources = ref([]);
const loading = ref(true);
const query = ref('');
const statusFilter = ref(route.query.status ?? '');

let debounceTimer;

function formatStatus(status) {
    return status?.replace('_', ' ') ?? status;
}

function formatDate(value) {
    if (!value) return '—';
    return new Date(value).toLocaleDateString();
}

function canDelete(resource) {
    return ['draft', 'pending_review'].includes(resource.status);
}

async function load() {
    loading.value = true;
    try {
        const { data } = await client.get('/my/resources', {
            params: {
                q: query.value || undefined,
                status: statusFilter.value || undefined,
            },
        });
        resources.value = data.data ?? [];
    } finally {
        loading.value = false;
    }
}

function debouncedLoad() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(load, 300);
}

async function remove(resource) {
    if (!confirm(`Delete "${resource.title}"?`)) return;
    await client.delete(`/my/resources/${resource.slug}`);
    await load();
}

watch(() => route.query.status, (value) => {
    statusFilter.value = value ?? '';
    load();
});

onMounted(load);
</script>
