<template>
    <div
        class="brand-logo bg-surface ring-app flex shrink-0 items-center justify-center overflow-hidden rounded-xl ring-1"
        :class="sizeClass"
    >
        <img
            :src="BRAND_LOGO_SRC"
            :alt="BRAND_LOGO_ALT"
            class="h-[88%] w-[88%] object-contain"
        >
    </div>
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
