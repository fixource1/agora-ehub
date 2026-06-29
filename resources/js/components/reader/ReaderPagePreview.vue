<template>
    <div
        ref="rootRef"
        class="reader-page-preview"
        :class="{ 'reader-page-preview--active': active, 'reader-page-preview--loading': loading }"
    >
        <img
            v-if="thumbnailUrl"
            :src="thumbnailUrl"
            :alt="`Page ${page} preview`"
            class="reader-page-preview__image"
            loading="lazy"
        >
        <div v-else class="reader-page-preview__placeholder">
            <span v-if="loading" class="reader-page-preview__spinner" aria-hidden="true" />
            <span v-else class="text-muted text-[11px] font-semibold">{{ page }}</span>
        </div>
    </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    page: {
        type: Number,
        required: true,
    },
    fetchThumbnail: {
        type: Function,
        default: null,
    },
    active: {
        type: Boolean,
        default: false,
    },
    eager: {
        type: Boolean,
        default: false,
    },
    ready: {
        type: Boolean,
        default: false,
    },
});

const rootRef = ref(null);
const thumbnailUrl = ref(null);
const loading = ref(false);
let observer = null;

async function loadThumbnail() {
    if (! props.fetchThumbnail || loading.value || thumbnailUrl.value || ! props.ready) {
        return;
    }

    loading.value = true;

    try {
        const url = await props.fetchThumbnail(props.page);

        if (url) {
            thumbnailUrl.value = url;
        }
    } finally {
        loading.value = false;
    }
}

function observePreview() {
    if (! rootRef.value || ! props.fetchThumbnail) {
        return;
    }

    if (props.eager) {
        loadThumbnail();

        return;
    }

    observer?.disconnect();
    observer = new IntersectionObserver(
        (entries) => {
            if (entries.some((entry) => entry.isIntersecting)) {
                loadThumbnail();
            }
        },
        { rootMargin: '160px' },
    );
    observer.observe(rootRef.value);
}

onMounted(observePreview);

watch(
    () => props.ready,
    (isReady) => {
        if (isReady) {
            loadThumbnail();
        }
    },
);

watch(
    () => props.page,
    () => {
        thumbnailUrl.value = null;
        loading.value = false;
        observePreview();
        if (props.ready) {
            loadThumbnail();
        }
    },
);

onBeforeUnmount(() => {
    observer?.disconnect();
});
</script>
