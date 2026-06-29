import workerSource from 'pdfjs-dist/legacy/build/pdf.worker.min.mjs?raw';

let workerBlobUrl = null;

/**
 * Configure PDF.js to use an inline worker blob.
 *
 * NativePHP's embedded server often cannot serve /pdf/*.mjs for dynamic import,
 * so we bundle the worker source and load it from a blob URL instead.
 */
export function configurePdfJsWorker(pdfjsLib) {
    if (! workerBlobUrl) {
        workerBlobUrl = URL.createObjectURL(
            new Blob([workerSource], { type: 'application/javascript' }),
        );
    }

    pdfjsLib.GlobalWorkerOptions.workerSrc = workerBlobUrl;
}

export function releasePdfJsWorkerUrl() {
    if (! workerBlobUrl) {
        return;
    }

    URL.revokeObjectURL(workerBlobUrl);
    workerBlobUrl = null;
}
