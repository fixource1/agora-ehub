<template>
    <div class="resource-toolbar">
        <div class="min-w-0">
            <template v-if="loading">
                <SkeletonBlock class="h-4 w-28" />
                <SkeletonBlock class="mt-2 h-3 w-36" />
            </template>
            <template v-else>
                <p class="text-app text-sm font-medium sm:text-base">
                    {{ count }} resources
                </p>
                <p
                    v-if="showRecentHint"
                    class="text-muted text-xs"
                >
                    Most recently opened first
                </p>
            </template>
        </div>
        <div class="resource-toolbar-actions">
            <ResourceViewToggle />
            <ResourceSortSelect
                v-if="showSort"
                :model-value="sortBy"
                @update:model-value="emit('update:sortBy', $event)"
            />
        </div>
    </div>
</template>

<script setup>
import ResourceViewToggle from '@/components/resources/ResourceViewToggle.vue';
import ResourceSortSelect from '@/components/resources/ResourceSortSelect.vue';
import SkeletonBlock from '@/components/skeleton/SkeletonBlock.vue';

defineProps({
    loading: { type: Boolean, default: false },
    count: { type: Number, required: true },
    showRecentHint: { type: Boolean, default: false },
    showSort: { type: Boolean, default: false },
    sortBy: { type: String, default: 'recent' },
});

const emit = defineEmits(['update:sortBy']);
</script>
