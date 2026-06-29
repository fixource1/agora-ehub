<template>
    <div class="pdf-reader-viewer flex min-h-0 flex-1 flex-col">
        <div
            v-if="engineLoading"
            class="text-muted flex min-h-0 flex-1 items-center justify-center text-sm"
        >
            Initializing PDF engine…
        </div>

        <div
            v-else-if="engineError"
            class="text-red-600 flex min-h-0 flex-1 items-center justify-center px-6 text-center text-sm"
        >
            {{ engineErrorMessage }}
        </div>

        <EmbedPDF
            v-else-if="engine && plugins.length"
            :engine="engine"
            :plugins="plugins"
            v-slot="{ activeDocumentId }"
        >
            <ReaderPdfDocument
                v-if="activeDocumentId"
                ref="documentRef"
                :document-id="activeDocumentId"
                :reading-mode="readingMode"
                @document-ready="emit('document-ready', $event)"
                @zoom-change="emit('zoom-change', $event)"
            >
                <template #footer="footerProps">
                    <slot
                        name="footer"
                        v-bind="footerProps"
                    />
                </template>
            </ReaderPdfDocument>
        </EmbedPDF>
    </div>
</template>

<script setup>
import { computed, provide, ref } from 'vue';
import { createPluginRegistration } from '@embedpdf/core';
import { EmbedPDF } from '@embedpdf/core/vue';
import { DocumentManagerPluginPackage } from '@embedpdf/plugin-document-manager/vue';
import { RenderPluginPackage } from '@embedpdf/plugin-render';
import { ScrollPluginPackage, ScrollStrategy } from '@embedpdf/plugin-scroll';
import { ViewportPluginPackage } from '@embedpdf/plugin-viewport';
import { ZoomMode, ZoomPluginPackage } from '@embedpdf/plugin-zoom';
import { isNativePlatform } from '@/config/api';
import { usePdfReaderEngine } from '@/composables/usePdfReaderEngine';
import ReaderPdfDocument from '@/components/reader/ReaderPdfDocument.vue';
import { READER_MAX_ZOOM, READER_MIN_ZOOM } from '@/lib/readerZoom';

const props = defineProps({
    source: {
        type: Object,
        required: true,
    },
    readingMode: {
        type: String,
        default: 'vertical',
    },
});

const emit = defineEmits(['document-ready', 'zoom-change']);

const documentRef = ref(null);

const { engine, isLoading: engineLoading, error: engineError } = usePdfReaderEngine();

provide('pdf-reader-engine', engine);

const engineErrorMessage = computed(() => {
    if (! engineError.value) {
        return 'Could not initialize the PDF reader.';
    }

    return engineError.value?.message ?? 'Could not initialize the PDF reader.';
});

const scrollStrategy = computed(() => (
    props.readingMode === 'horizontal' ? ScrollStrategy.Horizontal : ScrollStrategy.Vertical
));

const defaultZoomLevel = computed(() => (
    props.readingMode === 'horizontal' ? ZoomMode.FitPage : ZoomMode.FitWidth
));

const plugins = computed(() => {
    const initialDocument = props.source.kind === 'buffer'
        ? {
            documentId: props.source.documentId,
            name: props.source.name,
            buffer: props.source.buffer,
            autoActivate: true,
        }
        : {
            documentId: props.source.documentId,
            name: props.source.name,
            url: props.source.url,
            requestOptions: props.source.requestOptions,
            autoActivate: true,
        };

    return [
        createPluginRegistration(DocumentManagerPluginPackage, {
            initialDocuments: [initialDocument],
        }),
        createPluginRegistration(ViewportPluginPackage),
        createPluginRegistration(ScrollPluginPackage, {
            defaultPageGap: 12,
            defaultBufferSize: isNativePlatform() ? 1 : 3,
            defaultStrategy: scrollStrategy.value,
        }),
        createPluginRegistration(RenderPluginPackage),
        createPluginRegistration(ZoomPluginPackage, {
            defaultZoomLevel: defaultZoomLevel.value,
            minZoom: READER_MIN_ZOOM,
            maxZoom: READER_MAX_ZOOM,
        }),
    ];
});

function scrollToPage(pageNumber) {
    documentRef.value?.scrollToPage(pageNumber);
}

async function loadAnnotations() {
    return documentRef.value?.loadAnnotations() ?? [];
}

function getPageThumbnail(pageNumber) {
    return documentRef.value?.renderPageThumbnail(pageNumber) ?? Promise.resolve(null);
}

function refreshLayout() {
    documentRef.value?.refreshViewportZoom();
}

function zoomIn() {
    documentRef.value?.zoomIn();
}

function zoomOut() {
    documentRef.value?.zoomOut();
}

function scheduleLayoutRefresh() {
    [0, 50, 150, 350].forEach((delay) => {
        window.setTimeout(() => refreshLayout(), delay);
    });
}

defineExpose({
    scrollToPage,
    loadAnnotations,
    getPageThumbnail,
    zoomIn,
    zoomOut,
    refreshLayout: scheduleLayoutRefresh,
});
</script>
