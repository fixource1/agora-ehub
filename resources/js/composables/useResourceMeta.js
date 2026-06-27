import { computed } from 'vue';
import { getResourceTypeIcon } from '@/composables/resourceTypeIcons';

export function useResourceMeta(resource) {
    const typeSlug = computed(() => resource.value?.resource_type?.slug ?? 'other');
    const isVideo = computed(() => typeSlug.value === 'video');
    const isAudio = computed(() => typeSlug.value === 'audio');
    const typeIcon = computed(() => getResourceTypeIcon(typeSlug.value));

    const fileTypeLabel = computed(() => {
        const ext = resource.value?.primary_file?.file_type?.toUpperCase();
        if (ext) return ext;
        if (isVideo.value) return 'VIDEO';
        if (isAudio.value) return 'AUDIO';
        return resource.value?.resource_type?.name ?? 'FILE';
    });

    const metaLabel = computed(() => {
        const file = resource.value?.primary_file;
        const parts = [];
        if (file?.file_type) parts.push(file.file_type.toUpperCase());
        if (file?.file_size) parts.push(formatSize(file.file_size));
        if (!parts.length) return resource.value?.resource_type?.name ?? 'Resource';
        return parts.join(' • ');
    });

    const duration = computed(() => {
        const seconds = resource.value?.metadata?.duration_seconds;
        return seconds ? formatDuration(seconds) : null;
    });

    return { typeSlug, isVideo, isAudio, typeIcon, fileTypeLabel, metaLabel, duration };
}

function formatSize(bytes) {
    const mb = bytes / (1024 * 1024);
    return `${mb.toFixed(1)} MB`;
}

function formatDuration(seconds) {
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${m}:${String(s).padStart(2, '0')}`;
}
