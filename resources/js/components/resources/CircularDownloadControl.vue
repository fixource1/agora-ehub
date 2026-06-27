<template>
    <button
        type="button"
        class="circular-download-control"
        :class="[
            `circular-download-control--${size}`,
            `circular-download-control--${state}`,
            variant ? `circular-download-control--${variant}` : null,
        ]"
        :aria-label="ariaLabel"
        @click="$emit('click')"
    >
        <svg
            class="circular-download-control__ring"
            viewBox="0 0 32 32"
            aria-hidden="true"
        >
            <circle
                class="circular-download-control__track"
                cx="16"
                cy="16"
                :r="radius"
            />
            <circle
                v-if="state === 'downloading' || state === 'completed'"
                class="circular-download-control__progress"
                cx="16"
                cy="16"
                :r="radius"
                :stroke-dasharray="circumference"
                :stroke-dashoffset="progressOffset"
            />
        </svg>

        <span class="circular-download-control__icon">
            <IconStop v-if="state === 'downloading'" />
            <IconCheck v-else-if="state === 'completed'" />
            <IconDownload v-else />
        </span>
    </button>
</template>

<script setup>
import { computed } from 'vue';
import IconCheck from '@/components/icons/IconCheck.vue';
import IconDownload from '@/components/icons/IconDownload.vue';
import IconStop from '@/components/icons/IconStop.vue';

const props = defineProps({
    state: {
        type: String,
        default: 'idle',
        validator: (value) => ['idle', 'downloading', 'completed'].includes(value),
    },
    progress: { type: Number, default: 0 },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md'].includes(value),
    },
    variant: {
        type: String,
        default: null,
        validator: (value) => value === null || ['cover', 'button'].includes(value),
    },
    ariaLabel: { type: String, default: 'Download' },
});

defineEmits(['click']);

const radius = 14;
const circumference = 2 * Math.PI * radius;

const progressOffset = computed(() => {
    const amount = props.state === 'completed' ? 100 : props.progress;

    return circumference * (1 - amount / 100);
});
</script>
