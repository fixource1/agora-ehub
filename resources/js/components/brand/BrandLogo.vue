<template>
    <img
        :src="BRAND_LOGO_SRC"
        :alt="BRAND_LOGO_ALT"
        class="brand-logo shrink-0 object-contain"
        :class="sizeClass"
    >
</template>

<script setup>
import { computed } from 'vue';
import { BRAND_LOGO_ALT, BRAND_LOGO_SRC } from '@/constants/brand';

const props = defineProps({
    size: {
        type: String,
        default: 'md',
        validator: (v) => ['sm', 'md', 'lg', 'xl'].includes(v),
    },
});

const sizeClass = computed(() => ({
    sm: 'h-8 w-8',
    md: 'h-10 w-10',
    lg: 'h-14 w-14',
    xl: 'h-20 w-20',
}[props.size]));
</script>
