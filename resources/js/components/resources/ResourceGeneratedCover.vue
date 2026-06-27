<template>
    <div
        class="resource-generated-cover"
        :class="{
            'resource-generated-cover--compact': compact,
            'resource-generated-cover--detailed': showTitle,
        }"
        :style="coverStyle"
        aria-hidden="true"
    >
        <div class="resource-generated-cover__glow resource-generated-cover__glow--top" />
        <div class="resource-generated-cover__glow resource-generated-cover__glow--bottom" />
        <div class="resource-generated-cover__frame" />

        <div class="resource-generated-cover__icon">
            <component :is="icon" />
        </div>

        <div v-if="showTitle && titleLines.length" class="resource-generated-cover__title">
            <p v-for="(line, index) in titleLines" :key="index">{{ line }}</p>
        </div>

        <div v-if="showTitle && typeLabel" class="resource-generated-cover__type-badge">
            {{ typeLabel }}
        </div>

        <p v-if="showTitle && categoryLabel" class="resource-generated-cover__category">
            {{ categoryLabel }}
        </p>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { getCategoryPalette, getCoverTypeLabel, wrapCoverTitle } from '@/composables/resourceCoverArt';

const props = defineProps({
    icon: { type: Object, required: true },
    category: { type: String, default: null },
    categoryName: { type: String, default: null },
    title: { type: String, default: '' },
    typeSlug: { type: String, default: 'other' },
    fileType: { type: String, default: null },
    compact: { type: Boolean, default: false },
    showTitle: { type: Boolean, default: false },
});

const coverStyle = computed(() => {
    const [top, bottom] = getCategoryPalette(props.category);

    return {
        '--cover-top': top,
        '--cover-bottom': bottom,
        '--cover-text': top,
    };
});

const titleLines = computed(() => wrapCoverTitle(props.title));
const typeLabel = computed(() => getCoverTypeLabel(props.typeSlug, props.fileType));
const categoryLabel = computed(() => props.categoryName?.toUpperCase() ?? null);
</script>
