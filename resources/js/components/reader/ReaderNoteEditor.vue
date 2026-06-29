<template>
    <component :is="wrapperTag" v-bind="wrapperProps">
        <div
            v-if="open"
            class="reader-note-modal"
            :class="contained ? 'reader-note-modal--contained' : 'reader-note-modal--fixed'"
            @click.self="$emit('close')"
        >
            <div
                class="reader-note-modal__panel bg-surface safe-bottom shadow-2xl"
                role="dialog"
                aria-modal="true"
                :aria-label="`Note for page ${page}`"
            >
                <div class="reader-note-modal__header">
                    <p class="text-app font-semibold">Note for page {{ page }}</p>
                    <button type="button" class="text-muted text-sm" @click="$emit('close')">Cancel</button>
                </div>
                <textarea
                    :value="modelValue"
                    rows="4"
                    maxlength="1000"
                    class="web-input reader-note-modal__input w-full resize-none"
                    placeholder="Write your note..."
                    @input="$emit('update:modelValue', $event.target.value)"
                />
                <button
                    type="button"
                    class="bg-brand reader-note-modal__save w-full rounded-xl px-4 py-3 text-sm font-semibold text-white disabled:opacity-50"
                    :disabled="! modelValue.trim()"
                    @click="$emit('save')"
                >
                    Save note
                </button>
            </div>
        </div>
    </component>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    page: { type: Number, default: 1 },
    modelValue: { type: String, default: '' },
    contained: { type: Boolean, default: false },
});

defineEmits(['close', 'save', 'update:modelValue']);

const wrapperTag = computed(() => (props.contained ? 'div' : 'Teleport'));
const wrapperProps = computed(() => (props.contained ? {} : { to: 'body' }));
</script>
