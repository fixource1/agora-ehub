<template>
    <div class="px-8 py-8">
        <div class="border-app flex flex-wrap items-start justify-between gap-4 border-b pb-6">
            <div>
                <h1 class="text-app text-3xl font-bold">{{ isEdit ? 'Edit Resource' : 'Upload New Resource' }}</h1>
                <p class="text-muted mt-1">Share your knowledge. Upload any type of resource for your audience.</p>
            </div>
            <div class="flex gap-3">
                <button
                    type="button"
                    class="text-brand border-app rounded-xl border px-5 py-2.5 text-sm font-medium transition hover:bg-surface-muted"
                    :disabled="saving"
                    @click="save('draft')"
                >
                    Save as Draft
                </button>
                <button
                    type="button"
                    class="bg-brand rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand/90 disabled:opacity-60"
                    :disabled="saving"
                    @click="save('published')"
                >
                    Publish Resource
                </button>
            </div>
        </div>

        <div class="mt-8 flex gap-3 overflow-x-auto pb-2">
            <div
                v-for="(step, index) in steps"
                :key="step"
                class="flex shrink-0 items-center gap-2"
            >
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold"
                    :class="index === 0 ? 'bg-brand text-white' : 'web-step-inactive'"
                >
                    {{ index + 1 }}
                </div>
                <span class="text-sm" :class="index === 0 ? 'text-app font-medium' : 'text-muted'">{{ step }}</span>
                <span v-if="index < steps.length - 1" class="text-muted mx-2">—</span>
            </div>
        </div>

        <div v-if="error" class="web-error mt-6">{{ error }}</div>

        <div class="mt-8 grid gap-8 xl:grid-cols-[minmax(0,1fr)_320px]">
            <form class="space-y-8" @submit.prevent="save('draft')">
                <section class="web-card p-6">
                    <h2 class="text-app text-lg font-semibold">Basic Information</h2>

                    <label class="mt-5 block">
                        <span class="text-app mb-1.5 block text-sm font-medium">Title <span class="text-red-500">*</span></span>
                        <input v-model="form.title" type="text" required class="web-input" placeholder="Enter a title for your resource">
                    </label>

                    <label class="mt-4 block">
                        <span class="text-app mb-1.5 block text-sm font-medium">Subtitle (Optional)</span>
                        <input v-model="form.subtitle" type="text" class="web-input" placeholder="Add a subtitle that describes your resource">
                    </label>

                    <label class="mt-4 block">
                        <span class="text-app mb-1.5 block text-sm font-medium">Description</span>
                        <textarea
                            v-model="form.description"
                            rows="6"
                            maxlength="2000"
                            class="web-input resize-y"
                            placeholder="Describe your resource..."
                        />
                        <span class="text-muted mt-1 block text-right text-xs">{{ form.description.length }}/2000</span>
                    </label>
                </section>

                <section class="web-card p-6">
                    <h2 class="text-app text-lg font-semibold">Resource Type <span class="text-red-500">*</span></h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <button
                            v-for="type in resourceTypes"
                            :key="type.id"
                            type="button"
                            class="web-type-card rounded-xl p-4 text-left"
                            :class="form.resource_type_id === type.id ? 'web-type-card-selected' : ''"
                            @click="form.resource_type_id = type.id"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <span class="text-2xl" aria-hidden="true">{{ typeIcon(type.slug) }}</span>
                                <span
                                    class="mt-1 h-4 w-4 rounded-full border"
                                    :class="form.resource_type_id === type.id ? 'border-brand bg-brand' : 'border-app'"
                                />
                            </div>
                            <p class="text-app mt-3 font-medium">{{ type.name }}</p>
                            <p class="text-muted mt-1 text-xs">{{ type.description }}</p>
                        </button>
                    </div>
                </section>

                <section class="web-card p-6">
                    <h2 class="text-app text-lg font-semibold">Categorization & Metadata</h2>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="text-app mb-1.5 block text-sm font-medium">Category</span>
                            <select v-model="form.category_id" class="web-input">
                                <option :value="null">Select category</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </label>

                        <label class="block">
                            <span class="text-app mb-1.5 block text-sm font-medium">Tags (Optional)</span>
                            <input
                                v-model="tagsInput"
                                type="text"
                                class="web-input"
                                placeholder="Comma-separated, max 5"
                            >
                        </label>

                        <label class="block">
                            <span class="text-app mb-1.5 block text-sm font-medium">Language</span>
                            <select v-model="form.language" class="web-input">
                                <option value="en">English</option>
                                <option value="fil">Filipino</option>
                            </select>
                        </label>

                        <label class="block">
                            <span class="text-app mb-1.5 block text-sm font-medium">Primary Audience</span>
                            <select v-model="form.audience_level" class="web-input">
                                <option value="general">General</option>
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                                <option value="professional">Professional</option>
                            </select>
                        </label>
                    </div>
                </section>
            </form>

            <aside class="space-y-6">
                <section class="web-card p-5">
                    <h3 class="text-app font-semibold">Resource Preview</h3>
                    <div class="web-upload-zone mt-4 flex flex-col items-center justify-center rounded-xl p-6 text-center">
                        <img
                            v-if="coverPreview"
                            :src="coverPreview"
                            alt="Cover preview"
                            class="mb-4 max-h-48 w-full rounded-lg object-cover"
                        >
                        <p class="text-app text-sm font-medium">Upload Cover Image</p>
                        <p class="text-muted mt-1 text-xs">JPG, PNG, WEBP up to 5MB</p>
                        <label class="mt-4">
                            <span class="bg-brand cursor-pointer rounded-lg px-4 py-2 text-sm font-medium text-white">Choose File</span>
                            <input type="file" accept="image/*" class="hidden" @change="onCoverSelected">
                        </label>
                    </div>

                    <div class="border-app mt-4 rounded-lg border p-3">
                        <div class="flex gap-3">
                            <div class="bg-surface-muted h-16 w-12 shrink-0 rounded">
                                <img v-if="coverPreview" :src="coverPreview" alt="" class="h-full w-full rounded object-cover">
                            </div>
                            <div class="min-w-0">
                                <p class="text-app truncate text-sm font-medium">{{ form.title || 'Resource title' }}</p>
                                <p class="text-muted truncate text-xs">{{ form.subtitle || 'Subtitle preview' }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="web-card bg-surface-muted p-5">
                    <h3 class="text-app font-semibold">Tips for a great resource</h3>
                    <ul class="text-muted mt-3 space-y-2 text-sm">
                        <li>• Use a clear, descriptive title</li>
                        <li>• Write a detailed description</li>
                        <li>• Choose the correct resource type</li>
                        <li>• Add a high-quality cover image</li>
                    </ul>
                </section>
            </aside>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { client } from '../api/client';

const props = defineProps({
    slug: { type: String, default: null },
});

const router = useRouter();

const steps = ['Resource Details', 'Upload Files', 'Settings', 'Pricing', 'Review & Publish'];
const resourceTypes = ref([]);
const categories = ref([]);
const saving = ref(false);
const error = ref('');
const coverFile = ref(null);
const coverPreview = ref(null);
const tagsInput = ref('');

const form = reactive({
    title: '',
    subtitle: '',
    description: '',
    resource_type_id: null,
    category_id: null,
    language: 'en',
    audience_level: 'general',
});

const isEdit = computed(() => Boolean(props.slug));

const typeIcons = {
    ebook: '📖',
    'pdf-document': '📄',
    audio: '🎧',
    video: '🎬',
    other: '📦',
};

function typeIcon(slug) {
    return typeIcons[slug] ?? '📁';
}

function onCoverSelected(event) {
    const file = event.target.files?.[0];
    if (!file) return;
    coverFile.value = file;
    coverPreview.value = URL.createObjectURL(file);
}

function buildFormData(status) {
    const formData = new FormData();
    formData.append('title', form.title);
    if (form.subtitle) formData.append('subtitle', form.subtitle);
    if (form.description) formData.append('description', form.description);
    formData.append('resource_type_id', String(form.resource_type_id));
    if (form.category_id) formData.append('category_id', String(form.category_id));
    formData.append('language', form.language);
    formData.append('audience_level', form.audience_level);
    formData.append('status', status);

    tagsInput.value.split(',').map((t) => t.trim()).filter(Boolean).slice(0, 5).forEach((tag) => {
        formData.append('tags[]', tag);
    });

    if (coverFile.value) {
        formData.append('cover_image', coverFile.value);
    }

    return formData;
}

async function save(status) {
    error.value = '';

    if (!form.title.trim()) {
        error.value = 'Title is required.';
        return;
    }
    if (!form.resource_type_id) {
        error.value = 'Please select a resource type.';
        return;
    }

    saving.value = true;
    try {
        const formData = buildFormData(status);

        if (isEdit.value) {
            formData.append('_method', 'PUT');
            await client.post(`/my/resources/${props.slug}`, formData);
            router.push({ name: 'web.resources' });
        } else {
            const { data } = await client.post('/my/resources', formData);
            const created = data.data ?? data;
            router.push({ name: 'web.resources.edit', params: { slug: created.slug } });
        }
    } catch (e) {
        error.value = e.response?.data?.message
            ?? Object.values(e.response?.data?.errors ?? {}).flat().join(' ')
            ?? 'Unable to save resource.';
    } finally {
        saving.value = false;
    }
}

async function loadLookups() {
    const [typesRes, categoriesRes] = await Promise.all([
        client.get('/lookups/resource-types'),
        client.get('/lookups/categories'),
    ]);
    resourceTypes.value = typesRes.data.data ?? [];
    categories.value = categoriesRes.data.data ?? [];
    if (!form.resource_type_id && resourceTypes.value.length) {
        form.resource_type_id = resourceTypes.value[0].id;
    }
}

async function loadResource() {
    if (!props.slug) return;
    const { data } = await client.get(`/my/resources/${props.slug}`);
    const resource = data.data ?? data;
    form.title = resource.title ?? '';
    form.subtitle = resource.subtitle ?? '';
    form.description = resource.description ?? '';
    form.resource_type_id = resource.resource_type?.id ?? null;
    form.category_id = resource.category?.id ?? null;
    form.language = resource.language ?? 'en';
    form.audience_level = resource.audience_level ?? 'general';
    tagsInput.value = (resource.tags ?? []).join(', ');
    coverPreview.value = resource.cover_image;
}

onMounted(async () => {
    await loadLookups();
    if (isEdit.value) {
        await loadResource();
    }
});
</script>
