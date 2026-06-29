import bundledPdfiumWasmUrl from '@embedpdf/pdfium/pdfium.wasm?url';
import { getAgoraConfig, isNativePlatform, isRemoteMobileApi } from '@/config/api';

const PDFIUM_WASM_PATH = '/pdf/pdfium.wasm';
const PDFIUM_WASM_CDN = 'https://cdn.jsdelivr.net/npm/@embedpdf/pdfium@2.14.4/dist/pdfium.wasm';

function resolveAssetUrl(pathOrUrl) {
    if (! pathOrUrl) {
        return null;
    }

    if (pathOrUrl.startsWith('blob:') || pathOrUrl.startsWith('http://') || pathOrUrl.startsWith('https://')) {
        return pathOrUrl;
    }

    if (typeof window !== 'undefined' && window.location?.origin) {
        return new URL(pathOrUrl, window.location.origin).href;
    }

    return pathOrUrl;
}

/**
 * @returns {string[]}
 */
export function getPdfiumWasmCandidates() {
    const candidates = [];

    if (typeof window !== 'undefined' && window.location?.origin) {
        candidates.push(new URL(PDFIUM_WASM_PATH, window.location.origin).href);

        const bundled = resolveAssetUrl(bundledPdfiumWasmUrl);

        if (bundled) {
            candidates.push(bundled);
        }
    }

    if (isRemoteMobileApi()) {
        const base = String(getAgoraConfig().mobileApiBaseUrl ?? '').replace(/\/$/, '');

        if (base) {
            candidates.push(`${base}${PDFIUM_WASM_PATH}`);
        }
    }

    if (! isNativePlatform()) {
        candidates.push(PDFIUM_WASM_CDN);
    } else {
        candidates.push(PDFIUM_WASM_CDN);
    }

    return [...new Set(candidates.filter(Boolean))];
}

/**
 * @template T
 * @param {Promise<T>} promise
 * @param {number} timeoutMs
 * @param {string} message
 * @returns {Promise<T>}
 */
export function withTimeout(promise, timeoutMs, message) {
    return new Promise((resolve, reject) => {
        const timer = window.setTimeout(() => {
            reject(new Error(message));
        }, timeoutMs);

        promise
            .then((value) => {
                window.clearTimeout(timer);
                resolve(value);
            })
            .catch((error) => {
                window.clearTimeout(timer);
                reject(error);
            });
    });
}

/**
 * @param {import('@embedpdf/engines/dist/lib/pdfium/web/direct-engine').CreatePdfiumEngineOptions | undefined} options
 * @param {number} timeoutMs
 */
export async function createPdfiumEngineFromCandidates(options = {}, timeoutMs = 90000) {
    const { createPdfiumEngine } = await import('@embedpdf/engines/pdfium-direct-engine');
    const candidates = getPdfiumWasmCandidates();
    const failures = [];

    for (const url of candidates) {
        try {
            return await withTimeout(
                createPdfiumEngine(url, options),
                timeoutMs,
                'The PDF engine took too long to start.',
            );
        } catch (error) {
            failures.push({
                url,
                message: error instanceof Error ? error.message : String(error),
            });
        }
    }

    if (import.meta.env.DEV) {
        console.error('PDFium WASM load failures:', failures);
    }

    throw new Error('Could not load the PDF engine. Rebuild the app assets or check your network connection.');
}
