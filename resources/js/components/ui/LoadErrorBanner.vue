<template>
    <div
        v-if="error"
        class="bg-surface ring-app flex flex-col items-start gap-3 rounded-2xl p-4 ring-1 sm:flex-row sm:items-center sm:justify-between"
    >
        <p class="text-app text-sm">{{ error }}</p>
        <button
            type="button"
            class="text-brand shrink-0 text-sm font-medium"
            @click="$emit('retry')"
        >
            Try again
        </button>
    </div>
</template>

<script setup>
defineProps({
    error: { type: String, default: '' },
});

defineEmits(['retry']);
</script>
