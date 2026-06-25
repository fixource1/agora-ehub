<template>
    <div class="flex gap-2 overflow-x-auto pb-1 md:pt-1">
        <button
            v-for="filter in filters"
            :key="filter"
            class="shrink-0 rounded-full px-4 py-2 text-sm font-medium transition"
            :class="modelValue === filter
                ? 'bg-brand text-white shadow-sm'
                : 'bg-surface text-app ring-app ring-1'"
            @click="$emit('update:modelValue', filter)"
        >
            {{ filter }}
        </button>
    </div>
</template>

<script setup>
defineProps({
    modelValue: { type: String, required: true },
    filters: { type: Array, required: true },
});

defineEmits(['update:modelValue']);
</script>
