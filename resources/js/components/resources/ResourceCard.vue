<template>
    <RouterLink
        :to="{ name: 'resource.show', params: { slug: resource.slug } }"
        class="resource-card bg-surface ring-app group relative flex flex-col overflow-hidden rounded-xl shadow-sm ring-1 transition hover:shadow-md"
    >
        <div class="resource-card-cover relative aspect-[5/3] shrink-0 overflow-hidden">
            <img
                v-if="resource.cover_image"
                :src="resource.cover_image"
                :alt="resource.title"
                class="h-full w-full object-cover"
            >
            <div
                v-else
                class="resource-card-cover-inner flex h-full items-center justify-center"
            >
                <span class="resource-card-type-icon text-3xl md:text-4xl" aria-hidden="true">{{ typeEmoji }}</span>
            </div>

            <div
                v-if="isVideo"
                class="absolute inset-0 flex items-center justify-center bg-black/15"
            >
                <div class="bg-surface/95 text-brand flex h-9 w-9 items-center justify-center rounded-full shadow-sm">
                    <IconPlay class="h-4 w-4" />
                </div>
            </div>

            <span
                v-if="fileTypeLabel"
                class="resource-card-badge absolute bottom-2 left-2 rounded-md px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide"
            >
                {{ fileTypeLabel }}
            </span>

            <span
                v-if="duration"
                class="absolute bottom-2 right-2 rounded-md bg-black/70 px-2 py-0.5 text-[10px] font-medium text-white"
            >
                {{ duration }}
            </span>

            <span
                v-if="offline"
                class="resource-card-offline-badge absolute right-2 top-2 flex h-6 w-6 items-center justify-center rounded-full text-[11px] font-bold"
                title="Available offline"
            >
                ✓
            </span>
        </div>

        <div class="resource-card-body border-app flex min-h-0 flex-1 flex-col border-t px-3 py-2.5">
            <h3 class="text-app line-clamp-2 text-sm font-semibold leading-snug">{{ resource.title }}</h3>
            <p class="text-muted mt-1 truncate text-[11px]">
                {{ metaLabel }}
            </p>
        </div>
    </RouterLink>
</template>

<script setup>
import { computed } from 'vue';
import IconPlay from '@/components/icons/IconPlay.vue';

const props = defineProps({
    resource: { type: Object, required: true },
    offline: { type: Boolean, default: false },
});

const typeSlug = computed(() => props.resource.resource_type?.slug ?? 'other');
const isVideo = computed(() => typeSlug.value === 'video');
const isAudio = computed(() => typeSlug.value === 'audio');

const typeEmoji = computed(() => {
    if (isVideo.value) return '🎬';
    if (isAudio.value) return '🎧';
    if (typeSlug.value === 'pdf-document') return '📄';
    if (typeSlug.value === 'ebook') return '📚';
    return '📁';
});

const fileTypeLabel = computed(() => {
    const ext = props.resource.primary_file?.file_type?.toUpperCase();
    if (ext) return ext;
    if (isVideo.value) return 'VIDEO';
    if (isAudio.value) return 'AUDIO';
    return props.resource.resource_type?.name ?? 'FILE';
});

const metaLabel = computed(() => {
    const file = props.resource.primary_file;
    const parts = [];
    if (file?.file_type) parts.push(file.file_type.toUpperCase());
    if (file?.file_size) parts.push(formatSize(file.file_size));
    if (!parts.length) return props.resource.resource_type?.name ?? 'Resource';
    return parts.join(' • ');
});

const duration = computed(() => props.resource.metadata?.duration_seconds
    ? formatDuration(props.resource.metadata.duration_seconds)
    : null);

function formatSize(bytes) {
    const mb = bytes / (1024 * 1024);
    return `${mb.toFixed(1)} MB`;
}

function formatDuration(seconds) {
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${m}:${String(s).padStart(2, '0')}`;
}
</script>
