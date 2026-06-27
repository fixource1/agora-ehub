<template>
    <RouterLink
        :to="{ name: 'resource.show', params: { slug: resource.slug }, state: { resource } }"
        draggable="false"
        class="resource-card tap-feedback border-app bg-surface group relative flex flex-col overflow-hidden rounded-xl border transition hover:shadow-md"
        :class="longPress.pressClass"
        v-bind="longPress.handlers"
        @mouseenter="prefetchResource(resource.slug)"
    >
        <div class="resource-card-cover relative aspect-[3/4] shrink-0 overflow-hidden">
            <img
                v-if="showCoverImage"
                :src="resource.cover_image"
                :alt="resource.title"
                class="absolute inset-0 h-full w-full object-cover object-top transition duration-300 group-hover:scale-[1.02]"
                @error="coverImageFailed = true"
            >
            <ResourceGeneratedCover
                v-else
                :icon="typeIcon"
                :category="resource.category?.slug"
                :category-name="resource.category?.name"
                :title="resource.title"
                :type-slug="resource.resource_type?.slug"
                :file-type="resource.primary_file?.file_type"
                compact
            />

            <div
                v-if="isVideo && showCoverImage"
                class="absolute inset-0 z-10 flex items-center justify-center bg-black/15"
            >
                <div class="bg-surface/95 text-brand flex h-9 w-9 items-center justify-center rounded-full shadow-sm">
                    <IconPlay class="h-4 w-4" />
                </div>
            </div>

            <span
                v-if="fileTypeLabel"
                class="resource-card-badge absolute bottom-2 left-2 z-10 rounded-md px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide"
            >
                {{ fileTypeLabel }}
            </span>

            <span
                v-if="duration"
                class="absolute bottom-2 right-2 z-10 rounded-md bg-black/70 px-2 py-0.5 text-[10px] font-medium text-white"
            >
                {{ duration }}
            </span>

            <span
                v-if="offline"
                class="resource-card-offline-badge absolute right-2 top-2 z-10 flex h-6 w-6 items-center justify-center rounded-full"
                title="Available offline"
            >
                <IconCheck class="h-3.5 w-3.5" />
            </span>
        </div>

        <div class="resource-card-body flex min-h-0 flex-1 flex-col px-2.5 py-2.5">
            <h3 class="text-app line-clamp-2 text-xs font-semibold leading-snug sm:text-sm">{{ resource.title }}</h3>
            <p class="text-muted mt-1 truncate text-[10px] sm:text-[11px]">
                {{ metaLabel }}
            </p>
        </div>
    </RouterLink>
</template>

<script setup>
import { computed, ref, toRef, watch } from 'vue';
import IconCheck from '@/components/icons/IconCheck.vue';
import IconPlay from '@/components/icons/IconPlay.vue';
import ResourceGeneratedCover from '@/components/resources/ResourceGeneratedCover.vue';
import { useResourceMeta } from '@/composables/useResourceMeta';
import { useResourceCache } from '@/composables/useResourceCache';
import { useLongPress } from '@/composables/useLongPress';
import { useResourceQuickActions } from '@/composables/useResourceQuickActions';

const props = defineProps({
    resource: { type: Object, required: true },
    offline: { type: Boolean, default: false },
});

const { prefetchResource } = useResourceCache();
const { openQuickActions } = useResourceQuickActions();
const longPress = useLongPress(() => openQuickActions(props.resource));

const resourceRef = toRef(props, 'resource');
const { isVideo, typeIcon, fileTypeLabel, metaLabel, duration } = useResourceMeta(resourceRef);
const coverImageFailed = ref(false);

watch(() => props.resource.cover_image, () => {
    coverImageFailed.value = false;
});

const showCoverImage = computed(() => {
    if (coverImageFailed.value) {
        return false;
    }

    const cover = props.resource.cover_image;

    if (props.resource.offline_available) {
        return typeof cover === 'string' && cover.startsWith('blob:');
    }

    return Boolean(cover);
});
</script>
