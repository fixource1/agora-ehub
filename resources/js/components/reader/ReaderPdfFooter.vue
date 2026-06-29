<template>
    <footer class="reader-footer safe-bottom bg-surface border-app shrink-0 border-t px-4 py-3 lg:px-6">
        <div class="flex items-center gap-3">
            <button
                type="button"
                class="bg-surface-muted ring-app text-app tap-feedback flex h-10 w-10 shrink-0 items-center justify-center rounded-full ring-1 disabled:cursor-not-allowed disabled:opacity-40"
                :disabled="displayCurrentPage <= 1"
                aria-label="Previous page"
                @click="scrollToPreviousPage"
            >
                <IconChevronLeft class="h-5 w-5" />
            </button>

            <input
                :value="sliderPage"
                type="range"
                min="1"
                :max="maxPage"
                class="reader-range min-w-0 flex-1"
                @input="onSliderInput"
                @change="onSliderChange"
            >

            <button
                type="button"
                class="bg-surface-muted ring-app text-app tap-feedback flex h-10 w-10 shrink-0 items-center justify-center rounded-full ring-1 disabled:cursor-not-allowed disabled:opacity-40"
                :disabled="displayCurrentPage >= displayTotalPages"
                aria-label="Next page"
                @click="scrollToNextPage"
            >
                <IconChevronLeft class="h-5 w-5 rotate-180" />
            </button>
        </div>

        <p class="text-muted mt-2 text-center text-xs">
            Page {{ displayCurrentPage }} of {{ displayTotalPages }}
        </p>
    </footer>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import IconChevronLeft from '@/components/icons/IconChevronLeft.vue';

const props = defineProps({
    pageState: {
        type: Object,
        required: true,
    },
    scrollToPage: {
        type: Function,
        required: true,
    },
});

const emit = defineEmits(['page-state']);

const sliderPage = ref(1);
const isDragging = ref(false);

const displayCurrentPage = computed(() => Math.max(1, props.pageState.currentPage || 1));
const displayTotalPages = computed(() => Math.max(1, props.pageState.totalPages || 1));
const maxPage = computed(() => displayTotalPages.value);

watch(
    () => props.pageState,
    (state) => {
        emit('page-state', state);

        if (! isDragging.value) {
            sliderPage.value = displayCurrentPage.value;
        }
    },
    { deep: true, immediate: true },
);

function scrollToPreviousPage() {
    if (displayCurrentPage.value <= 1) {
        return;
    }

    props.scrollToPage(displayCurrentPage.value - 1);
}

function scrollToNextPage() {
    if (displayCurrentPage.value >= displayTotalPages.value) {
        return;
    }

    props.scrollToPage(displayCurrentPage.value + 1);
}

function onSliderInput(event) {
    isDragging.value = true;
    sliderPage.value = Number(event.target.value);
}

function onSliderChange(event) {
    const page = Number(event.target.value);
    props.scrollToPage(page);
    isDragging.value = false;
}
</script>
