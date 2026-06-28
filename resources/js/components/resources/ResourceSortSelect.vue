<template>
    <ActionMenu
        aria-label="Sort resources"
        align="right"
        title="Sort by"
        trigger-class="resource-sort-select tap-feedback"
    >
        <template #trigger>
            <span class="resource-sort-select__label">{{ selectedLabel }}</span>
            <svg
                class="resource-sort-select__chevron"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </template>

        <template #default="{ close }">
            <button
                v-for="option in options"
                :key="option.value"
                type="button"
                class="action-menu__item"
                role="menuitemradio"
                :aria-checked="modelValue === option.value"
                @click="select(option.value, close)"
            >
                <span class="action-menu__item-check">
                    <IconCheck v-if="modelValue === option.value" class="h-4 w-4" />
                </span>
                {{ option.label }}
            </button>
        </template>
    </ActionMenu>
</template>

<script setup>
import { computed } from 'vue';
import ActionMenu from '@/components/ui/ActionMenu.vue';
import IconCheck from '@/components/icons/IconCheck.vue';

const props = defineProps({
    modelValue: { type: String, required: true },
    options: {
        type: Array,
        default: () => [
            { value: 'recent', label: 'Recent' },
            { value: 'title', label: 'Title' },
        ],
    },
});

const emit = defineEmits(['update:modelValue']);

const selectedLabel = computed(() => {
    return props.options.find((option) => option.value === props.modelValue)?.label ?? 'Sort';
});

function select(value, close) {
    if (value !== props.modelValue) {
        emit('update:modelValue', value);
    }

    close();
}
</script>
