<template>
    <div
        class="resource-cover-placeholder"
        :class="{ 'resource-cover-placeholder--compact': compact }"
        aria-hidden="true"
    >
        <div class="resource-cover-placeholder__icon">
            <component :is="icon" :class="compact ? 'h-4 w-4' : 'h-7 w-7 sm:h-8 sm:w-8'" />
        </div>
    </div>
</template>

<script setup>
defineProps({
    icon: { type: Object, required: true },
    compact: { type: Boolean, default: false },
});
</script>
