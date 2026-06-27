<template>
    <div>
        <slot name="toolbar" :layout="layout" />

        <ResourceCollectionSkeleton v-if="loading" />

        <div
            v-else-if="resources.length"
            class="resource-collection"
            :class="layoutClass"
        >
            <template v-if="layout === 'list'">
                <ResourceListItem
                    v-for="resource in resources"
                    :key="resource.id"
                    :resource="resource"
                    :offline="offline?.(resource)"
                />
            </template>
            <template v-else>
                <ResourceCard
                    v-for="resource in resources"
                    :key="resource.id"
                    :resource="resource"
                    :offline="offline?.(resource)"
                />
            </template>
        </div>

        <div v-else class="resource-collection-empty">
            <slot name="empty" />
        </div>
    </div>
</template>

<script setup>
import ResourceCard from '@/components/resources/ResourceCard.vue';
import ResourceListItem from '@/components/resources/ResourceListItem.vue';
import ResourceCollectionSkeleton from '@/components/skeleton/ResourceCollectionSkeleton.vue';
import { useResourceLayout } from '@/composables/useResourceLayout';

defineProps({
    resources: { type: Array, required: true },
    loading: { type: Boolean, default: false },
    offline: { type: Function, default: null },
});

const { layout, layoutClass } = useResourceLayout();
</script>
