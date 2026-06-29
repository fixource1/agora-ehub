<template>
    <div class="px-8 py-8 pb-28">
        <div class="border-app border-b pb-6">
            <h1 class="text-app text-3xl font-bold">{{ isEdit ? 'Edit User' : 'Add User' }}</h1>
            <p class="text-muted mt-1">{{ isEdit ? 'Update account details and role.' : 'Create a new author portal account.' }}</p>
        </div>

        <div v-if="error" class="web-error mt-6">{{ error }}</div>

        <form class="web-card mt-8 max-w-2xl space-y-5 p-6" @submit.prevent="save">
            <label class="block">
                <span class="text-app mb-1.5 block text-sm font-medium">Name <span class="text-red-500">*</span></span>
                <input v-model="form.name" type="text" required class="web-input">
            </label>

            <label class="block">
                <span class="text-app mb-1.5 block text-sm font-medium">Email <span class="text-red-500">*</span></span>
                <input v-model="form.email" type="email" required class="web-input">
            </label>

            <label class="block">
                <span class="text-app mb-1.5 block text-sm font-medium">
                    Password
                    <span v-if="!isEdit" class="text-red-500">*</span>
                    <span v-else class="text-muted font-normal">(leave blank to keep current)</span>
                </span>
                <input v-model="form.password" type="password" :required="!isEdit" minlength="8" class="web-input">
            </label>

            <label class="block">
                <span class="text-app mb-1.5 block text-sm font-medium">Role <span class="text-red-500">*</span></span>
                <select v-model="form.role" required class="web-input">
                    <option value="contributor">Contributor</option>
                    <option value="administrator">Administrator</option>
                </select>
            </label>

            <label class="block">
                <span class="text-app mb-1.5 block text-sm font-medium">Institution</span>
                <input v-model="form.institution" type="text" class="web-input">
            </label>

            <label class="block">
                <span class="text-app mb-1.5 block text-sm font-medium">Department</span>
                <input v-model="form.department" type="text" class="web-input">
            </label>

            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit" class="bg-brand rounded-xl px-5 py-2.5 text-sm font-semibold text-white" :disabled="saving">
                    {{ saving ? 'Saving...' : 'Save User' }}
                </button>
                <RouterLink to="/author/admin/users" class="web-input inline-flex items-center rounded-xl px-5 py-2.5 text-sm font-medium">
                    Cancel
                </RouterLink>
            </div>
        </form>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { client } from '../api/client';

const props = defineProps({
    id: {
        type: [String, Number],
        default: null,
    },
});

const route = useRoute();
const router = useRouter();
const saving = ref(false);
const error = ref('');
const isEdit = computed(() => Boolean(props.id ?? route.params.id));

const form = reactive({
    name: '',
    email: '',
    password: '',
    role: 'contributor',
    institution: '',
    department: '',
});

async function load() {
    if (!isEdit.value) {
        return;
    }

    const userId = props.id ?? route.params.id;
    const { data } = await client.get(`/admin/users/${userId}`);
    const user = data.data ?? data;

    form.name = user.name ?? '';
    form.email = user.email ?? '';
    form.role = user.role ?? 'contributor';
    form.institution = user.institution ?? '';
    form.department = user.department ?? '';
}

async function save() {
    saving.value = true;
    error.value = '';

    try {
        const payload = {
            name: form.name,
            email: form.email,
            role: form.role,
            institution: form.institution || null,
            department: form.department || null,
        };

        if (form.password) {
            payload.password = form.password;
        }

        if (isEdit.value) {
            const userId = props.id ?? route.params.id;
            await client.put(`/admin/users/${userId}`, payload);
        } else {
            if (!form.password) {
                error.value = 'Password is required for new users.';
                return;
            }
            payload.password = form.password;
            await client.post('/admin/users', payload);
        }

        router.push({ name: 'web.admin.users' });
    } catch (err) {
        const messages = err.response?.data?.errors;
        if (messages) {
            error.value = Object.values(messages).flat().join(' ');
        } else {
            error.value = err.response?.data?.message ?? 'Failed to save user.';
        }
    } finally {
        saving.value = false;
    }
}

onMounted(async () => {
    try {
        await load();
    } catch (err) {
        error.value = err.response?.data?.message ?? 'Failed to load user.';
    }
});
</script>
