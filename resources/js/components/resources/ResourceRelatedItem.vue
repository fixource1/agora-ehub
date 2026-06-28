<template>
    <RouterLink
        :to="{ name: 'resource.show', params: { slug: resource.slug }, state: { resource } }"
        class="resource-related-item tap-feedback flex items-center gap-3 px-4 py-3 lg:px-5"
        @mouseenter="prefetchResource(resource.slug)"
    >
        <div class="resource-related-item__cover relative aspect-[3/4] h-12 w-9 shrink-0 overflow-hidden rounded-md">
            <img
                v-if="showCoverImage"
                :src="coverImageUrl"
                :alt="resource.title"
                class="absolute inset-0 h-full w-full object-cover object-center"
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
        </div>

        <div class="min-w-0 flex-1">
            <p class="text-app line-clamp-2 text-sm font-medium leading-snug">{{ resource.title }}</p>
            <p v-if="authorName" class="text-muted mt-0.5 truncate text-xs">{{ authorName }}</p>
        </div>

        <span class="text-muted shrink-0 text-sm" aria-hidden="true">›</span>
    </RouterLink>
</template>

<script setup>
import { computed, toRef } from 'vue';
import ResourceGeneratedCover from '@/components/resources/ResourceGeneratedCover.vue';
import { useResourceMeta } from '@/composables/useResourceMeta';
import { useResourceCover } from '@/composables/useResourceCover';
import { useResourceCache } from '@/composables/useResourceCache';

const props = defineProps({
    resource: { type: Object, required: true },
});

const { prefetchResource } = useResourceCache();
const resourceRef = toRef(props, 'resource');
const { typeIcon } = useResourceMeta(resourceRef);
const { coverImageUrl, showCoverImage, coverImageFailed } = useResourceCover(resourceRef);

const authorName = computed(() => props.resource.authors?.[0]?.name ?? props.resource.category?.name ?? null);
</script>
