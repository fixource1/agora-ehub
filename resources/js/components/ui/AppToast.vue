<template>
    <Transition name="app-toast">
        <p
            v-if="message"
            class="app-toast"
            role="status"
            aria-live="polite"
        >
            {{ message }}
        </p>
    </Transition>
</template>

<script setup>
import { useToast } from '@/composables/useToast';

const { message } = useToast();
</script>
