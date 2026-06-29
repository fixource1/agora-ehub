<template>
    <div class="px-8 py-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-app text-3xl font-bold">Categories</h1>
                <p class="text-muted mt-1">Organize resources by topic</p>
            </div>
            <RouterLink to="/author/admin/categories/new" class="bg-brand rounded-xl px-5 py-2.5 text-sm font-semibold text-white">
                + Add Category
            </RouterLink>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <input
                v-model="query"
                type="search"
                placeholder="Search categories..."
                class="web-input max-w-xs"
                @input="debouncedLoad"
            >
            <select v-model="activeFilter" class="web-input w-auto" @change="load">
                <option value="">All</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>

        <div v-if="error" class="web-error mt-6">{{ error }}</div>
        <div v-if="loading" class="text-muted mt-10 text-center text-sm">Loading categories...</div>

        <div v-else-if="categories.length" class="web-card mt-6 overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="web-table-head">
                    <tr>
                        <th class="px-5 py-3 font-medium">Name</th>
                        <th class="px-5 py-3 font-medium">Slug</th>
                        <th class="px-5 py-3 font-medium">Parent</th>
                        <th class="px-5 py-3 font-medium">Resources</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="category in categories" :key="category.id" class="web-table-row last:border-0">
                        <td class="text-app px-5 py-4 font-medium">{{ category.name }}</td>
                        <td class="text-muted px-5 py-4">{{ category.slug }}</td>
                        <td class="text-muted px-5 py-4">{{ category.parent?.name ?? '—' }}</td>
                        <td class="text-muted px-5 py-4">{{ category.resources_count ?? 0 }}</td>
                        <td class="px-5 py-4">
                            <span class="web-status-badge" :class="category.is_active ? 'web-status-published' : 'web-status-archived'">
                                {{ category.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <RouterLink
                                :to="{ name: 'web.admin.categories.edit', params: { id: category.id } }"
                                class="text-brand mr-3 text-sm font-medium hover:underline"
                            >
                                Edit
                            </RouterLink>
                            <button
                                class="text-sm text-red-600 hover:underline dark:text-red-400"
                                @click="remove(category)"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else class="web-card border-dashed mt-10 p-12 text-center">
            <p class="text-app font-medium">No categories found</p>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { client } from '../api/client';

const categories = ref([]);
const loading = ref(true);
const error = ref('');
const query = ref('');
const activeFilter = ref('');

let debounceTimer;

async function load() {
    loading.value = true;
    error.value = '';
    try {
        const params = {
            q: query.value || undefined,
        };

        if (activeFilter.value !== '') {
            params.is_active = activeFilter.value === '1';
        }

        const { data } = await client.get('/admin/categories', { params });
        categories.value = data.data ?? [];
    } catch (err) {
        error.value = err.response?.data?.message ?? 'Failed to load categories.';
    } finally {
        loading.value = false;
    }
}

function debouncedLoad() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(load, 300);
}

async function remove(category) {
    if (!confirm(`Delete "${category.name}"?`)) {
        return;
    }
    await client.delete(`/admin/categories/${category.id}`);
    await load();
}

onMounted(load);
</script>
