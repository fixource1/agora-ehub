<template>
    <Transition name="reader-floating-zoom-fade">
        <div
            v-show="visible"
            class="reader-floating-zoom pointer-events-none absolute z-20 flex flex-col items-end gap-2"
            :class="placementClass"
        >
        <div
            class="reader-floating-zoom__cluster bg-surface ring-app pointer-events-auto flex items-center gap-1 rounded-full p-1 shadow-lg ring-1"
            @pointerdown.stop
        >
            <button
                type="button"
                class="reader-floating-zoom__button text-app tap-feedback flex h-9 w-9 items-center justify-center rounded-full disabled:cursor-not-allowed disabled:opacity-40"
                :disabled="! canZoomOut"
                aria-label="Zoom out"
                @click.stop="$emit('zoom-out')"
            >
                <IconZoomOut class="h-4 w-4" />
            </button>
            <button
                type="button"
                class="reader-floating-zoom__label text-muted tap-feedback min-w-[3rem] rounded-full px-2 py-1 text-center text-xs font-semibold"
                :title="'Reset zoom (' + zoomPercent + ')'"
                @click.stop="$emit('reset')"
            >
                {{ zoomPercent }}
            </button>
            <button
                type="button"
                class="reader-floating-zoom__button text-app tap-feedback flex h-9 w-9 items-center justify-center rounded-full disabled:cursor-not-allowed disabled:opacity-40"
                :disabled="! canZoomIn"
                aria-label="Zoom in"
                @click.stop="$emit('zoom-in')"
            >
                <IconZoomIn class="h-4 w-4" />
            </button>
        </div>
        </div>
    </Transition>
</template>

<script setup>
import { computed } from 'vue';
import IconZoomIn from '@/components/icons/IconZoomIn.vue';
import IconZoomOut from '@/components/icons/IconZoomOut.vue';
import { formatZoomPercent } from '@/lib/readerZoom';

const props = defineProps({
    zoomState: {
        type: Object,
        default: () => ({ currentZoomLevel: 1 }),
    },
    canZoomIn: {
        type: Boolean,
        default: true,
    },
    canZoomOut: {
        type: Boolean,
        default: true,
    },
    visible: {
        type: Boolean,
        default: true,
    },
    placement: {
        type: String,
        default: 'bottom-left',
    },
});

defineEmits(['zoom-in', 'zoom-out', 'reset']);

const zoomPercent = computed(() => formatZoomPercent(props.zoomState?.currentZoomLevel));

const placementClass = computed(() => (
    props.placement === 'bottom-left'
        ? 'bottom-4 left-4 sm:bottom-5 sm:left-5'
        : 'bottom-4 right-4 sm:bottom-5 sm:right-5'
));
</script>
