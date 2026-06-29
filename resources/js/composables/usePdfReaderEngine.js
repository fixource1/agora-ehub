import { ignore } from '@embedpdf/models';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { isNativePlatform } from '@/config/api';
import { createPdfiumEngineFromCandidates } from '@/lib/loadPdfiumWasm';

const ENGINE_INIT_TIMEOUT_MS = 90000;

export function usePdfReaderEngine() {
    const engine = ref(null);
    const isLoading = ref(true);
    const error = ref(null);

    onMounted(loadEngine);
    onBeforeUnmount(destroyEngine);

    async function loadEngine() {
        isLoading.value = true;
        error.value = null;

        try {
            engine.value = await createPdfiumEngineFromCandidates(
                {
                    fontFallback: isNativePlatform() ? null : undefined,
                    encoderPoolSize: isNativePlatform() ? 0 : undefined,
                },
                ENGINE_INIT_TIMEOUT_MS,
            );
        } catch (loadError) {
            error.value = loadError instanceof Error
                ? loadError
                : new Error('Could not initialize the PDF reader.');
        } finally {
            isLoading.value = false;
        }
    }

    function destroyEngine() {
        const current = engine.value;

        current?.closeAllDocuments?.().wait(() => {
            current?.destroy?.();
        }, ignore);

        engine.value = null;
    }

    return {
        engine,
        isLoading,
        error,
    };
}
