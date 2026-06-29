<template>
    <aside class="bg-surface border-app flex h-full flex-col border-l p-5">
        <div class="mb-4 flex items-center justify-between">
            <p class="text-app font-semibold">Reading</p>
            <button
                v-if="closable"
                type="button"
                class="text-muted tap-feedback flex h-8 w-8 items-center justify-center rounded-full"
                @click="$emit('close')"
            >
                ✕
            </button>
        </div>

        <p class="text-muted mb-2 text-xs font-medium uppercase tracking-wide">Layout</p>
        <p v-if="horizontalDisabled" class="text-muted mb-2 text-xs">
            Horizontal layout is available in landscape.
        </p>
        <ReaderLayoutToggle
            class="mb-5"
            :model-value="readingMode"
            :horizontal-disabled="horizontalDisabled"
            @update:model-value="$emit('update:readingMode', $event)"
        />

        <p class="text-muted mb-2 text-xs font-medium uppercase tracking-wide">Display</p>
        <button
            type="button"
            class="ring-app bg-surface-muted text-app tap-feedback flex w-full items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-medium ring-1"
            @click="$emit('update:isFullscreen', ! isFullscreen)"
        >
            <IconReaderExpand v-if="! isFullscreen" class="h-4 w-4" />
            <IconReaderCompress v-else class="h-4 w-4" />
            {{ isFullscreen ? 'Exit full screen' : 'Full screen' }}
        </button>
    </aside>
</template>

<script setup>
import IconReaderCompress from '@/components/icons/IconReaderCompress.vue';
import IconReaderExpand from '@/components/icons/IconReaderExpand.vue';
import ReaderLayoutToggle from '@/components/reader/ReaderLayoutToggle.vue';

defineProps({
    readingMode: { type: String, default: 'vertical' },
    horizontalDisabled: { type: Boolean, default: false },
    isFullscreen: { type: Boolean, default: false },
    closable: { type: Boolean, default: false },
});

defineEmits(['update:readingMode', 'update:isFullscreen', 'close']);
</script>
