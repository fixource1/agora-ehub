<template>
    <div
        class="reader-layout-toggle bg-surface ring-app inline-flex w-full rounded-2xl p-1 ring-1"
        role="group"
        aria-label="Reading layout"
    >
        <button
            v-for="option in options"
            :key="option.id"
            type="button"
            class="reader-layout-toggle-btn tap-feedback flex flex-1 items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium disabled:cursor-not-allowed disabled:opacity-40"
            :class="modelValue === option.id ? 'bg-brand text-white shadow-sm' : 'text-muted'"
            :aria-pressed="modelValue === option.id"
            :disabled="option.id === 'horizontal' && horizontalDisabled"
            @click="$emit('update:modelValue', option.id)"
        >
            <component :is="option.icon" class="h-4 w-4 shrink-0" />
            {{ option.label }}
        </button>
    </div>
</template>

<script setup>
import IconReaderHorizontal from '@/components/icons/IconReaderHorizontal.vue';
import IconReaderVertical from '@/components/icons/IconReaderVertical.vue';

defineProps({
    modelValue: {
        type: String,
        default: 'vertical',
    },
    horizontalDisabled: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['update:modelValue']);

const options = [
    { id: 'vertical', label: 'Vertical', icon: IconReaderVertical },
    { id: 'horizontal', label: 'Horizontal', icon: IconReaderHorizontal },
];
</script>
