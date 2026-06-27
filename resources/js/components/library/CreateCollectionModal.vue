<template>
    <Teleport to="body">
        <Transition name="fade">
            <button
                v-if="open"
                type="button"
                class="fixed inset-0 z-50 bg-black/40"
                aria-label="Close dialog"
                @click="close"
            />
        </Transition>

        <Transition name="slide-up">
            <div
                v-if="open"
                class="fixed inset-x-0 bottom-0 z-50 flex justify-center p-4 sm:inset-0 sm:items-center"
                @click.self="close"
            >
                <form
                    class="bg-surface ring-app w-full max-w-md rounded-2xl p-5 shadow-xl ring-1 sm:p-6"
                    @submit.prevent="submit"
                >
                    <h2 class="text-app text-lg font-semibold">New collection</h2>
                    <p class="text-muted mt-1 text-sm">
                        Group related resources together for quick access.
                    </p>

                    <label class="mt-5 block">
                        <span class="text-app mb-1.5 block text-sm font-medium">Name</span>
                        <input
                            ref="nameInput"
                            v-model="name"
                            type="text"
                            maxlength="80"
                            required
                            autocomplete="off"
                            class="collection-modal-input"
                            placeholder="e.g. Research references"
                            @keydown.esc="close"
                        >
                    </label>

                    <p v-if="error" class="text-maroon mt-2 text-sm">{{ error }}</p>

                    <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            class="collection-modal-button collection-modal-button--secondary"
                            @click="close"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="collection-modal-button collection-modal-button--primary"
                            :disabled="!canSubmit"
                        >
                            Create collection
                        </button>
                    </div>
                </form>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { computed, nextTick, ref, watch } from 'vue';

const props = defineProps({
    open: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'create']);

const name = ref('');
const error = ref('');
const nameInput = ref(null);

const canSubmit = computed(() => name.value.trim().length > 0);

watch(() => props.open, async (isOpen) => {
    if (! isOpen) {
        return;
    }

    name.value = '';
    error.value = '';
    await nextTick();
    nameInput.value?.focus();
});

function close() {
    emit('close');
}

function submit() {
    const trimmed = name.value.trim();

    if (! trimmed) {
        error.value = 'Enter a collection name.';
        return;
    }

    emit('create', trimmed);
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-up-enter-active,
.slide-up-leave-active {
    transition: transform 0.25s ease, opacity 0.25s ease;
}

.slide-up-enter-from,
.slide-up-leave-to {
    transform: translateY(1rem);
    opacity: 0;
}

@media (min-width: 640px) {
    .slide-up-enter-from,
    .slide-up-leave-to {
        transform: translateY(0) scale(0.98);
    }
}
</style>
