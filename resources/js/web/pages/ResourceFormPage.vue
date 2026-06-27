<template>
    <div class="px-8 py-8 pb-28">
        <div class="border-app flex flex-wrap items-start justify-between gap-4 border-b pb-6">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-app text-3xl font-bold">{{ isEdit ? 'Edit Resource' : 'Upload New Resource' }}</h1>
                    <span
                        v-if="currentStatus"
                        class="web-status-badge"
                        :class="`web-status-${currentStatus}`"
                    >
                        {{ formatStatus(currentStatus) }}
                    </span>
                </div>
                <p class="text-muted mt-1">
                    {{ isEdit ? 'Update details, files, and publishing status.' : 'Add your resource details and files on one page.' }}
                </p>
            </div>
        </div>

        <div v-if="error" class="web-error mt-6">{{ error }}</div>
        <div v-if="notice" class="web-notice mt-6">{{ notice }}</div>

        <div class="mt-8 grid gap-8 xl:grid-cols-[minmax(0,1fr)_320px]">
            <form class="space-y-6" @submit.prevent="save('draft')">
                <section class="web-card p-6">
                    <h2 class="text-app text-lg font-semibold">Essentials</h2>
                    <p class="text-muted mt-1 text-sm">Title and resource type are required.</p>

                    <label class="mt-5 block">
                        <span class="text-app mb-1.5 block text-sm font-medium">Title <span class="text-red-500">*</span></span>
                        <input v-model="form.title" type="text" required class="web-input" placeholder="Enter a title for your resource">
                    </label>

                    <label class="mt-4 block">
                        <span class="text-app mb-1.5 block text-sm font-medium">Subtitle (optional)</span>
                        <input v-model="form.subtitle" type="text" class="web-input" placeholder="Add a subtitle that describes your resource">
                    </label>

                    <div class="mt-6">
                        <span class="text-app mb-3 block text-sm font-medium">Resource type <span class="text-red-500">*</span></span>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            <button
                                v-for="type in resourceTypes"
                                :key="type.id"
                                type="button"
                                class="web-type-card rounded-xl p-4 text-left"
                                :class="form.resource_type_id === type.id ? 'web-type-card-selected' : ''"
                                @click="form.resource_type_id = type.id"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <div
                                        class="web-type-card-icon"
                                        :class="form.resource_type_id === type.id ? 'web-type-card-icon--selected' : ''"
                                    >
                                        <component :is="getResourceTypeIcon(type.slug)" class="h-6 w-6" />
                                    </div>
                                    <span
                                        class="web-type-card-check mt-1 flex h-4 w-4 items-center justify-center rounded-full border"
                                        :class="form.resource_type_id === type.id ? 'border-brand bg-brand text-white' : 'border-app'"
                                    >
                                        <svg
                                            v-if="form.resource_type_id === type.id"
                                            class="h-2.5 w-2.5"
                                            viewBox="0 0 12 12"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            aria-hidden="true"
                                        >
                                            <path d="M2 6l3 3 5-5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                </div>
                                <p class="text-app mt-3 font-medium">{{ type.name }}</p>
                                <p class="text-muted mt-1 text-xs leading-relaxed">{{ type.description }}</p>
                            </button>
                        </div>
                    </div>
                </section>

                <section class="web-card p-6">
                    <h2 class="text-app text-lg font-semibold">Files</h2>
                    <p class="text-muted mt-1 text-sm">Upload the main file readers will open or download.</p>

                    <div class="web-upload-zone mt-5 rounded-xl p-6">
                        <div class="flex flex-col items-center text-center sm:flex-row sm:items-start sm:text-left">
                            <div class="bg-surface-muted flex h-12 w-12 shrink-0 items-center justify-center rounded-xl">
                                <IconDocument class="text-brand h-6 w-6" />
                            </div>
                            <div class="mt-4 min-w-0 sm:mt-0 sm:ml-4 sm:flex-1">
                                <p class="text-app text-sm font-medium">Primary file</p>
                                <p class="text-muted mt-1 text-xs">PDF, Office docs, video, audio, or ZIP up to 50MB</p>

                                <div v-if="primaryFileLabel" class="border-app bg-surface-muted mt-4 rounded-lg border px-3 py-2 text-left">
                                    <p class="text-app truncate text-sm font-medium">{{ primaryFileLabel }}</p>
                                    <p v-if="primaryFileSizeLabel" class="text-muted text-xs">{{ primaryFileSizeLabel }}</p>
                                </div>

                                <label class="mt-4 inline-block">
                                    <span class="bg-brand cursor-pointer rounded-lg px-4 py-2 text-sm font-medium text-white">
                                        {{ primaryFileLabel ? 'Replace file' : 'Choose file' }}
                                    </span>
                                    <input
                                        type="file"
                                        class="hidden"
                                        accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.mp4,.mp3,.zip,.epub"
                                        @change="onPrimaryFileSelected"
                                    >
                                </label>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="web-card overflow-hidden">
                    <button
                        type="button"
                        class="text-app flex w-full items-center justify-between p-6 text-left"
                        @click="detailsOpen = !detailsOpen"
                    >
                        <div>
                            <h2 class="text-lg font-semibold">Details</h2>
                            <p class="text-muted mt-1 text-sm">Description, category, tags, and audience (optional)</p>
                        </div>
                        <svg
                            class="text-muted h-5 w-5 shrink-0 transition-transform"
                            :class="detailsOpen ? 'rotate-180' : ''"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div v-show="detailsOpen" class="border-app space-y-4 border-t px-6 pt-5 pb-6">
                        <label class="block">
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

                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span class="text-app mb-1.5 block text-sm font-medium">Category</span>
                                <select v-model="form.category_id" class="web-input">
                                    <option :value="null">Select category</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                            </label>

                            <label class="block">
                                <span class="text-app mb-1.5 block text-sm font-medium">Tags (optional)</span>
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
                                <span class="text-app mb-1.5 block text-sm font-medium">Primary audience</span>
                                <select v-model="form.audience_level" class="web-input">
                                    <option value="general">General</option>
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="advanced">Advanced</option>
                                    <option value="professional">Professional</option>
                                </select>
                            </label>
                        </div>
                    </div>
                </section>
            </form>

            <aside class="space-y-6">
                <section class="web-card p-5">
                    <div class="flex items-center gap-2">
                        <IconUpload class="text-brand h-5 w-5 shrink-0" />
                        <h3 class="text-app font-semibold">Cover image</h3>
                    </div>
                    <div class="web-upload-zone mt-4 flex flex-col items-center justify-center rounded-xl p-6 text-center">
                        <img
                            v-if="coverPreview"
                            :src="coverPreview"
                            alt="Cover preview"
                            class="mb-4 max-h-48 w-full rounded-lg object-cover"
                        >
                        <IconUpload v-else class="text-muted mb-3 h-10 w-10" />
                        <p class="text-app text-sm font-medium">Upload cover image</p>
                        <p class="text-muted mt-1 text-xs">JPG, PNG, WEBP up to 5MB</p>
                        <label class="mt-4">
                            <span class="bg-brand cursor-pointer rounded-lg px-4 py-2 text-sm font-medium text-white">Choose file</span>
                            <input type="file" accept="image/*" class="hidden" @change="onCoverSelected">
                        </label>
                    </div>

                    <div class="border-app mt-4 rounded-lg border p-3">
                        <div class="flex gap-3">
                            <div class="bg-surface-muted flex h-16 w-12 shrink-0 items-center justify-center overflow-hidden rounded">
                                <img v-if="coverPreview" :src="coverPreview" alt="" class="h-full w-full object-cover">
                                <IconBooks v-else class="text-muted h-5 w-5" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-app truncate text-sm font-medium">{{ form.title || 'Resource title' }}</p>
                                <p class="text-muted truncate text-xs">{{ form.subtitle || 'Subtitle preview' }}</p>
                                <p v-if="selectedTypeName" class="text-muted mt-1 truncate text-[11px]">{{ selectedTypeName }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="web-card bg-surface-muted p-5">
                    <div class="flex items-center gap-2">
                        <IconLightBulb class="text-brand h-5 w-5 shrink-0" />
                        <h3 class="text-app font-semibold">Tips</h3>
                    </div>
                    <ul class="text-muted mt-3 space-y-2.5 text-sm">
                        <li class="flex gap-2">
                            <span class="text-brand shrink-0">•</span>
                            <span>Use a clear, descriptive title</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-brand shrink-0">•</span>
                            <span>Attach the primary file before submitting for review</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-brand shrink-0">•</span>
                            <span>Choose the correct resource type</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-brand shrink-0">•</span>
                            <span>Add a high-quality cover image</span>
                        </li>
                    </ul>
                </section>
            </aside>
        </div>

        <div class="web-form-actions border-app fixed inset-x-0 bottom-0 z-20 border-t px-8 py-4">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-end gap-3">
                <button
                    type="button"
                    class="text-brand border-app rounded-xl border px-5 py-2.5 text-sm font-medium transition hover:bg-surface-muted disabled:opacity-60"
                    :disabled="saving"
                    @click="save('draft')"
                >
                    Save as draft
                </button>
                <button
                    v-if="canSubmitForReview"
                    type="button"
                    class="bg-brand rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand/90 disabled:opacity-60"
                    :disabled="saving"
                    @click="save('pending_review')"
                >
                    Submit for review
                </button>
                <button
                    v-if="canPublish"
                    type="button"
                    class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-60"
                    :disabled="saving"
                    @click="save('published')"
                >
                    Publish
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import IconBooks from '@/components/icons/IconBooks.vue';
import IconDocument from '@/components/icons/IconDocument.vue';
import IconLightBulb from '@/components/icons/IconLightBulb.vue';
import IconUpload from '@/components/icons/IconUpload.vue';
import { getResourceTypeIcon } from '../composables/resourceTypeIcons';
import { client } from '../api/client';
import { useAuthStore } from '../stores/auth';

const props = defineProps({
    slug: { type: String, default: null },
});

const router = useRouter();
const auth = useAuthStore();

const resourceTypes = ref([]);
const categories = ref([]);
const saving = ref(false);
const error = ref('');
const notice = ref('');
const coverFile = ref(null);
const coverPreview = ref(null);
const primaryFile = ref(null);
const existingPrimaryFile = ref(null);
const currentStatus = ref('draft');
const detailsOpen = ref(true);
const tagsInput = ref('');
const resourceSlug = ref(props.slug);

const form = reactive({
    title: '',
    subtitle: '',
    description: '',
    resource_type_id: null,
    category_id: null,
    language: 'en',
    audience_level: 'general',
});

const isEdit = computed(() => Boolean(resourceSlug.value));

const selectedTypeName = computed(() =>
    resourceTypes.value.find((type) => type.id === form.resource_type_id)?.name ?? null,
);

const canManageResources = computed(() => Boolean(auth.user?.can_manage_resources));

const canSubmitForReview = computed(() =>
    !['published', 'archived'].includes(currentStatus.value),
);

const canPublish = computed(() => canManageResources.value);

const primaryFileLabel = computed(() => {
    if (primaryFile.value) return primaryFile.value.name;
    return existingPrimaryFile.value?.file_name ?? null;
});

const primaryFileSizeLabel = computed(() => {
    if (primaryFile.value) return formatBytes(primaryFile.value.size);
    if (existingPrimaryFile.value?.file_size) {
        return formatBytes(existingPrimaryFile.value.file_size);
    }
    return null;
});

function formatStatus(status) {
    return status?.replace('_', ' ') ?? status;
}

function formatBytes(bytes) {
    if (!bytes) return '';
    const units = ['B', 'KB', 'MB', 'GB'];
    let size = bytes;
    let unit = 0;
    while (size >= 1024 && unit < units.length - 1) {
        size /= 1024;
        unit += 1;
    }
    return `${size.toFixed(unit === 0 ? 0 : 1)} ${units[unit]}`;
}

function onCoverSelected(event) {
    const file = event.target.files?.[0];
    if (!file) return;
    coverFile.value = file;
    coverPreview.value = URL.createObjectURL(file);
}

function onPrimaryFileSelected(event) {
    const file = event.target.files?.[0];
    if (!file) return;
    primaryFile.value = file;
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

    if (primaryFile.value) {
        formData.append('primary_file', primaryFile.value);
    }

    return formData;
}

async function save(status) {
    error.value = '';
    notice.value = '';

    if (!form.title.trim()) {
        error.value = 'Title is required.';
        return;
    }
    if (!form.resource_type_id) {
        error.value = 'Please select a resource type.';
        return;
    }

    const needsPrimaryFile = status === 'pending_review' || status === 'published';
    if (needsPrimaryFile && !primaryFile.value && !existingPrimaryFile.value) {
        error.value = 'Please upload a primary file before submitting or publishing.';
        return;
    }

    saving.value = true;
    try {
        const formData = buildFormData(status);

        if (isEdit.value) {
            formData.append('_method', 'PUT');
            const { data } = await client.post(`/my/resources/${resourceSlug.value}`, formData);
            const resource = data.data ?? data;
            applyResource(resource);
            notice.value = status === 'draft'
                ? 'Draft saved.'
                : status === 'pending_review'
                    ? 'Submitted for review.'
                    : 'Resource published.';
            if (status !== 'draft') {
                router.push({ name: 'web.resources' });
            }
        } else {
            const { data } = await client.post('/my/resources', formData);
            const created = data.data ?? data;
            resourceSlug.value = created.slug;
            currentStatus.value = created.status ?? status;
            existingPrimaryFile.value = created.primary_file ?? existingPrimaryFile.value;
            primaryFile.value = null;
            notice.value = status === 'draft'
                ? 'Draft created. You can keep editing and add files.'
                : status === 'pending_review'
                    ? 'Submitted for review.'
                    : 'Resource published.';
            await router.replace({
                name: 'web.resources.edit',
                params: { slug: created.slug },
            });
            if (status !== 'draft') {
                router.push({ name: 'web.resources' });
            }
        }
    } catch (e) {
        error.value = e.response?.data?.message
            ?? Object.values(e.response?.data?.errors ?? {}).flat().join(' ')
            ?? 'Unable to save resource.';
    } finally {
        saving.value = false;
    }
}

function applyResource(resource) {
    form.title = resource.title ?? '';
    form.subtitle = resource.subtitle ?? '';
    form.description = resource.description ?? '';
    form.resource_type_id = resource.resource_type?.id ?? null;
    form.category_id = resource.category?.id ?? null;
    form.language = resource.language ?? 'en';
    form.audience_level = resource.audience_level ?? 'general';
    currentStatus.value = resource.status ?? 'draft';
    tagsInput.value = (resource.tags ?? []).join(', ');
    coverPreview.value = resource.cover_image;
    existingPrimaryFile.value = resource.primary_file ?? null;
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
    if (!resourceSlug.value) return;
    const { data } = await client.get(`/my/resources/${resourceSlug.value}`);
    applyResource(data.data ?? data);
    detailsOpen.value = Boolean(form.description || form.category_id || tagsInput.value);
}

onMounted(async () => {
    if (!auth.user) {
        await auth.initialize();
    }
    await loadLookups();
    if (isEdit.value) {
        await loadResource();
    }
});
</script>
