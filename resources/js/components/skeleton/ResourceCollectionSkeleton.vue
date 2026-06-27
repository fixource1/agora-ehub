<template>
    <div class="resource-collection" :class="layoutClass">
        <template v-if="layout === 'list'">
            <ResourceListItemSkeleton v-for="index in count" :key="index" />
        </template>
        <template v-else>
            <ResourceCardSkeleton v-for="index in count" :key="index" />
        </template>
    </div>
</template>

<script setup>
import ResourceCardSkeleton from '@/components/skeleton/ResourceCardSkeleton.vue';
import ResourceListItemSkeleton from '@/components/skeleton/ResourceListItemSkeleton.vue';
import { useResourceLayout } from '@/composables/useResourceLayout';

defineProps({
    count: { type: Number, default: 8 },
});

const { layout, layoutClass } = useResourceLayout();
</script>
