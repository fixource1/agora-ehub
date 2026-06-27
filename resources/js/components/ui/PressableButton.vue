<template>
    <button
        type="button"
        class="tap-feedback"
        :class="[icon ? 'tap-feedback--icon' : null, attrs.class]"
        draggable="false"
        v-bind="elementBindings"
        @click="$emit('click', $event)"
    >
        <slot />
    </button>
</template>

<script setup>
import { computed, useAttrs } from 'vue';

defineProps({
    icon: { type: Boolean, default: false },
});

defineEmits(['click']);

defineOptions({
    inheritAttrs: false,
});

const attrs = useAttrs();

const elementBindings = computed(() => {
    const { class: _class, onClick: _onClick, ...rest } = attrs;

    return rest;
});
</script>
