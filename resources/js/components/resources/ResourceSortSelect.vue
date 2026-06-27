<template>
    <div class="resource-sort-select bg-surface ring-app relative inline-flex h-9 items-center rounded-xl ring-1">
        <label :for="selectId" class="sr-only">Sort resources</label>
        <select
            :id="selectId"
            :value="modelValue"
            class="resource-sort-select-input text-app h-full min-w-[5.25rem] cursor-pointer appearance-none bg-transparent pl-3 pr-7 text-xs font-medium outline-none sm:min-w-[5.5rem] sm:text-sm"
            @change="$emit('update:modelValue', $event.target.value)"
        >
            <option
                v-for="option in options"
                :key="option.value"
                :value="option.value"
            >
                {{ option.label }}
            </option>
        </select>
        <svg
            class="text-muted pointer-events-none absolute right-2 top-1/2 h-4 w-4 -translate-y-1/2"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="2"
            stroke="currentColor"
            aria-hidden="true"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
    </div>
</template>

<script setup>
import { useId } from 'vue';

defineProps({
    modelValue: { type: String, required: true },
    options: {
        type: Array,
        default: () => [
            { value: 'recent', label: 'Recent' },
            { value: 'title', label: 'Title' },
        ],
    },
});

defineEmits(['update:modelValue']);

const selectId = useId();
</script>
