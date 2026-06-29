import { getOfflineResource, resolveDownloadUrl } from '@/composables/useOfflineStore';

function buildAuthHeaders() {
    return {
        Accept: 'application/pdf,application/octet-stream,*/*',
        'X-Requested-With': 'XMLHttpRequest',
        'X-Device-Id': window.axios.defaults.headers.common['X-Device-Id'] ?? '',
        'X-Device-Name': window.axios.defaults.headers.common['X-Device-Name'] ?? '',
    };
}

function resolveAbsoluteUrl(url) {
    const baseUrl = (window.axios.defaults.baseURL || '').replace(/\/$/, '');

    if (url.startsWith('http')) {
        return url;
    }

    return `${baseUrl}${url.startsWith('/') ? url : `/${url}`}`;
}

export function isPdfResource(resource) {
    return resource?.primary_file?.file_type?.toLowerCase() === 'pdf';
}

export function canReadPdfResource(resource, { offline = false } = {}) {
    if (! isPdfResource(resource)) {
        return false;
    }

    if (offline) {
        return true;
    }

    return Boolean(resolveDownloadUrl(resource));
}

/**
 * @param {string} slug
 * @param {{ resource?: object }} [options]
 * @returns {Promise<{ kind: 'buffer' | 'url', documentId: string, name: string, buffer?: ArrayBuffer, url?: string, requestOptions?: object }>}
 */
export async function resolvePdfDocumentSource(slug, { resource: prefetched } = {}) {
    const offline = await getOfflineResource(slug);

    if (offline?.offline_file_url) {
        const response = await fetch(offline.offline_file_url);

        if (! response.ok) {
            throw new Error('Could not read the offline PDF.');
        }

        const buffer = await response.arrayBuffer();

        if (! buffer.byteLength) {
            throw new Error('The offline PDF file is empty.');
        }

        return {
            kind: 'buffer',
            documentId: slug,
            name: offline.offline_file_name ?? offline.primary_file?.file_name ?? `${slug}.pdf`,
            buffer,
        };
    }

    let resource = prefetched;

    if (! resource?.primary_file) {
        const { data } = await window.axios.get(`/resources/${slug}`);
        resource = data.data;
    }

    const downloadPath = resolveDownloadUrl(resource);

    if (! downloadPath) {
        throw new Error('This resource has no downloadable PDF file.');
    }

    return {
        kind: 'url',
        documentId: slug,
        name: resource.primary_file?.file_name ?? `${slug}.pdf`,
        url: resolveAbsoluteUrl(downloadPath),
        requestOptions: {
            headers: buildAuthHeaders(),
        },
    };
}
