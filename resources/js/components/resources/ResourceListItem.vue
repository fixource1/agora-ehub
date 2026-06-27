<template>
    <RouterLink
        :to="{ name: 'resource.show', params: { slug: resource.slug }, state: { resource } }"
        draggable="false"
        class="resource-list-item tap-feedback bg-surface ring-app group flex items-stretch gap-3 overflow-hidden rounded-xl p-2.5 ring-1 transition hover:bg-surface-muted sm:gap-4 sm:p-3"
        :class="longPress.pressClass"
        v-bind="longPress.handlers"
        @mouseenter="prefetchResource(resource.slug)"
    >
        <div
            class="resource-list-item-cover relative aspect-[3/4] w-14 shrink-0 overflow-hidden rounded-lg sm:w-16"
        >
            <img
                v-if="showCoverImage"
                :src="resource.cover_image"
                :alt="resource.title"
                class="absolute inset-0 h-full w-full object-cover object-top"
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
            <span
                v-if="offline"
                class="resource-card-offline-badge absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full"
                title="Available offline"
            >
                <IconCheck class="h-3 w-3" />
            </span>
        </div>

        <div class="flex min-w-0 flex-1 flex-col justify-center py-0.5">
            <div class="flex items-start gap-2">
                <h3 class="text-app line-clamp-2 flex-1 text-sm font-semibold leading-snug">{{ resource.title }}</h3>
                <span
                    v-if="fileTypeLabel"
                    class="resource-card-badge shrink-0 rounded-md px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wide"
                >
                    {{ fileTypeLabel }}
                </span>
            </div>
            <p class="text-muted mt-1 truncate text-xs">{{ metaLabel }}</p>
            <p v-if="resource.subtitle" class="text-muted mt-0.5 line-clamp-1 text-[11px]">{{ resource.subtitle }}</p>
        </div>
    </RouterLink>
</template>

<script setup>
import { computed, ref, toRef, watch } from 'vue';
import IconCheck from '@/components/icons/IconCheck.vue';
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
const { typeIcon, fileTypeLabel, metaLabel } = useResourceMeta(resourceRef);
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
