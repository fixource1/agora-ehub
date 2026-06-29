<template>
    <div class="px-8 py-8 pb-28">
        <div class="border-app border-b pb-6">
            <h1 class="text-app text-3xl font-bold">{{ isEdit ? 'Edit Category' : 'Add Category' }}</h1>
            <p class="text-muted mt-1">{{ isEdit ? 'Update category details.' : 'Create a new resource category.' }}</p>
        </div>

        <div v-if="error" class="web-error mt-6">{{ error }}</div>

        <form class="web-card mt-8 max-w-2xl space-y-5 p-6" @submit.prevent="save">
            <label class="block">
                <span class="text-app mb-1.5 block text-sm font-medium">Name <span class="text-red-500">*</span></span>
                <input v-model="form.name" type="text" required class="web-input">
            </label>

            <label class="block">
                <span class="text-app mb-1.5 block text-sm font-medium">Slug</span>
                <input v-model="form.slug" type="text" class="web-input" placeholder="Auto-generated from name if empty">
            </label>

            <label class="block">
                <span class="text-app mb-1.5 block text-sm font-medium">Parent category</span>
                <select v-model="form.parent_id" class="web-input">
                    <option :value="null">None</option>
                    <option
                        v-for="category in parentOptions"
                        :key="category.id"
                        :value="category.id"
                    >
                        {{ category.name }}
                    </option>
                </select>
            </label>

            <label class="block">
                <span class="text-app mb-1.5 block text-sm font-medium">Description</span>
                <textarea v-model="form.description" rows="3" class="web-input"></textarea>
            </label>

            <label class="block">
                <span class="text-app mb-1.5 block text-sm font-medium">Icon</span>
                <input v-model="form.icon" type="text" class="web-input" placeholder="Optional icon identifier">
            </label>

            <label class="block">
                <span class="text-app mb-1.5 block text-sm font-medium">Sort order</span>
                <input v-model.number="form.sort_order" type="number" min="0" class="web-input">
            </label>

            <label class="flex items-center gap-3">
                <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300">
                <span class="text-app text-sm font-medium">Active</span>
            </label>

            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit" class="bg-brand rounded-xl px-5 py-2.5 text-sm font-semibold text-white" :disabled="saving">
                    {{ saving ? 'Saving...' : 'Save Category' }}
                </button>
                <RouterLink to="/author/admin/categories" class="web-input inline-flex items-center rounded-xl px-5 py-2.5 text-sm font-medium">
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
const parentOptions = ref([]);
const isEdit = computed(() => Boolean(props.id ?? route.params.id));

const form = reactive({
    name: '',
    slug: '',
    parent_id: null,
    description: '',
    icon: '',
    sort_order: 0,
    is_active: true,
});

async function loadParentOptions() {
    const { data } = await client.get('/admin/categories', { params: { per_page: 100 } });
    const items = data.data ?? [];
    const currentId = Number(props.id ?? route.params.id);

    parentOptions.value = items.filter((category) => category.id !== currentId);
}

async function load() {
    if (!isEdit.value) {
        return;
    }

    const categoryId = props.id ?? route.params.id;
    const { data } = await client.get(`/admin/categories/${categoryId}`);
    const category = data.data ?? data;

    form.name = category.name ?? '';
    form.slug = category.slug ?? '';
    form.parent_id = category.parent_id ?? null;
    form.description = category.description ?? '';
    form.icon = category.icon ?? '';
    form.sort_order = category.sort_order ?? 0;
    form.is_active = category.is_active ?? true;
}

async function save() {
    saving.value = true;
    error.value = '';

    try {
        const payload = {
            name: form.name,
            slug: form.slug || null,
            parent_id: form.parent_id || null,
            description: form.description || null,
            icon: form.icon || null,
            sort_order: form.sort_order ?? 0,
            is_active: form.is_active,
        };

        if (isEdit.value) {
            const categoryId = props.id ?? route.params.id;
            await client.put(`/admin/categories/${categoryId}`, payload);
        } else {
            await client.post('/admin/categories', payload);
        }

        router.push({ name: 'web.admin.categories' });
    } catch (err) {
        const messages = err.response?.data?.errors;
        if (messages) {
            error.value = Object.values(messages).flat().join(' ');
        } else {
            error.value = err.response?.data?.message ?? 'Failed to save category.';
        }
    } finally {
        saving.value = false;
    }
}

onMounted(async () => {
    try {
        await loadParentOptions();
        await load();
    } catch (err) {
        error.value = err.response?.data?.message ?? 'Failed to load category.';
    }
});
</script>
