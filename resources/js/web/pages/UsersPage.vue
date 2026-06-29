<template>
    <div class="px-8 py-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-app text-3xl font-bold">Users</h1>
                <p class="text-muted mt-1">Manage author portal accounts</p>
            </div>
            <RouterLink to="/author/admin/users/new" class="bg-brand rounded-xl px-5 py-2.5 text-sm font-semibold text-white">
                + Add User
            </RouterLink>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <input
                v-model="query"
                type="search"
                placeholder="Search users..."
                class="web-input max-w-xs"
                @input="debouncedLoad"
            >
            <select v-model="roleFilter" class="web-input w-auto" @change="load">
                <option value="">All roles</option>
                <option value="administrator">Administrator</option>
                <option value="contributor">Contributor</option>
            </select>
        </div>

        <div v-if="error" class="web-error mt-6">{{ error }}</div>
        <div v-if="loading" class="text-muted mt-10 text-center text-sm">Loading users...</div>

        <div v-else-if="users.length" class="web-card mt-6 overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="web-table-head">
                    <tr>
                        <th class="px-5 py-3 font-medium">Name</th>
                        <th class="px-5 py-3 font-medium">Email</th>
                        <th class="px-5 py-3 font-medium">Role</th>
                        <th class="px-5 py-3 font-medium">Institution</th>
                        <th class="px-5 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users" :key="user.id" class="web-table-row last:border-0">
                        <td class="text-app px-5 py-4 font-medium">{{ user.name }}</td>
                        <td class="text-muted px-5 py-4">{{ user.email }}</td>
                        <td class="px-5 py-4">
                            <span class="web-status-badge" :class="user.role === 'administrator' ? 'web-status-published' : 'web-status-draft'">
                                {{ formatRole(user.role) }}
                            </span>
                        </td>
                        <td class="text-muted px-5 py-4">{{ user.institution || '—' }}</td>
                        <td class="px-5 py-4 text-right">
                            <RouterLink
                                :to="{ name: 'web.admin.users.edit', params: { id: user.id } }"
                                class="text-brand mr-3 text-sm font-medium hover:underline"
                            >
                                Edit
                            </RouterLink>
                            <button
                                v-if="canDelete(user)"
                                class="text-sm text-red-600 hover:underline dark:text-red-400"
                                @click="remove(user)"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else class="web-card border-dashed mt-10 p-12 text-center">
            <p class="text-app font-medium">No users found</p>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import { client } from '../api/client';

const auth = useAuthStore();
const users = ref([]);
const loading = ref(true);
const error = ref('');
const query = ref('');
const roleFilter = ref('');

let debounceTimer;

function formatRole(role) {
    if (role === 'administrator') {
        return 'Administrator';
    }
    return 'Contributor';
}

function canDelete(user) {
    return user.id !== auth.user?.id;
}

async function load() {
    loading.value = true;
    error.value = '';
    try {
        const { data } = await client.get('/admin/users', {
            params: {
                q: query.value || undefined,
                role: roleFilter.value || undefined,
            },
        });
        users.value = data.data ?? [];
    } catch (err) {
        error.value = err.response?.data?.message ?? 'Failed to load users.';
    } finally {
        loading.value = false;
    }
}

function debouncedLoad() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(load, 300);
}

async function remove(user) {
    if (!confirm(`Delete "${user.name}"?`)) {
        return;
    }
    await client.delete(`/admin/users/${user.id}`);
    await load();
}

onMounted(load);
</script>
