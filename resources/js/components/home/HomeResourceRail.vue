<template>
    <section v-if="loading || resources.length" class="home-section">
        <div class="home-section-header">
            <div class="min-w-0">
                <h2 class="text-app text-base font-semibold md:text-lg">{{ title }}</h2>
                <p v-if="subtitle" class="text-muted mt-0.5 text-xs md:text-sm">{{ subtitle }}</p>
            </div>
            <button
                v-if="seeAllLabel && !loading"
                type="button"
                class="text-brand shrink-0 text-sm font-medium"
                @click="$emit('see-all')"
            >
                {{ seeAllLabel }}
            </button>
        </div>

        <div v-if="loading" class="home-resource-rail scrollbar-hide">
            <ResourceCardSkeleton
                v-for="index in 4"
                :key="index"
                class="home-resource-rail-card"
            />
        </div>

        <div v-else class="home-resource-rail scrollbar-hide">
            <ResourceCard
                v-for="resource in resources"
                :key="resource.id"
                :resource="resource"
                :offline="offline?.(resource)"
                class="home-resource-rail-card"
            />
        </div>
    </section>
</template>

<script setup>
import ResourceCard from '@/components/resources/ResourceCard.vue';
import ResourceCardSkeleton from '@/components/skeleton/ResourceCardSkeleton.vue';

defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
    resources: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    seeAllLabel: { type: String, default: '' },
    offline: { type: Function, default: null },
});

defineEmits(['see-all']);
</script>
