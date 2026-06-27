<template>
    <div class="skeleton" :class="rounded" />
</template>

<script setup>
defineProps({
    rounded: { type: String, default: 'rounded' },
});
</script>
