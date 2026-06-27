<template>
    <div
        class="resource-view-toggle bg-surface ring-app inline-flex rounded-xl p-1 ring-1"
        role="group"
        aria-label="Resource layout"
    >
        <button
            v-for="option in layouts"
            :key="option.id"
            type="button"
            class="resource-view-toggle-btn tap-feedback rounded-lg px-2.5 py-1.5 sm:px-3"
            :class="layout === option.id ? 'bg-brand text-white shadow-sm' : 'text-muted hover:text-app'"
            :title="option.title"
            :aria-pressed="layout === option.id"
            @click="setLayout(option.id)"
        >
            <span class="sr-only">{{ option.label }}</span>
            <component
                :is="iconComponents[option.id]"
                class="resource-view-toggle-icon h-[1.125rem] w-[1.125rem] sm:h-5 sm:w-5"
            />
        </button>
    </div>
</template>

<script setup>
import IconLayoutGrid4 from '@/components/icons/IconLayoutGrid4.vue';
import IconLayoutGrid6 from '@/components/icons/IconLayoutGrid6.vue';
import IconLayoutList from '@/components/icons/IconLayoutList.vue';
import { useResourceLayout } from '@/composables/useResourceLayout';

const { layout, layouts, setLayout } = useResourceLayout();

const iconComponents = {
    books: IconLayoutGrid6,
    'books-large': IconLayoutGrid4,
    list: IconLayoutList,
};
</script>
