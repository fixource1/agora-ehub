import { idbDeleteByPrefix, idbGet, idbPut } from '@/lib/idb';

const META_STORAGE_KEY = 'agora-offline-meta';
const LEGACY_DOWNLOADS_KEY = 'agora-downloaded-slugs';

const objectUrls = new Map();

function fileKey(slug) {
    return `file:${slug}`;
}

function coverKey(slug) {
    return `cover:${slug}`;
}

function loadMetaIndex() {
    try {
        const stored = localStorage.getItem(META_STORAGE_KEY);

        if (! stored) {
            return {};
        }

        const parsed = JSON.parse(stored);

        if (! parsed || typeof parsed !== 'object') {
            return {};
        }

        return repairMetaIndex(parsed);
    } catch {
        return {};
    }
}

function repairMetaIndex(index) {
    let dirty = false;

    for (const slug of Object.keys(index)) {
        const resource = index[slug]?.resource;

        if (! resource) {
            continue;
        }

        if (typeof resource.cover_image === 'string' && resource.cover_image.startsWith('blob:')) {
            delete resource.cover_image;
            dirty = true;
        }
    }

    if (dirty) {
        saveMetaIndex(index);
    }

    return index;
}

function saveMetaIndex(index) {
    localStorage.setItem(META_STORAGE_KEY, JSON.stringify(index));
}

function revokeObjectUrl(key) {
    const existing = objectUrls.get(key);

    if (existing) {
        URL.revokeObjectURL(existing);
        objectUrls.delete(key);
    }
}

function rememberObjectUrl(key, blob) {
    revokeObjectUrl(key);

    const url = URL.createObjectURL(blob);
    objectUrls.set(key, url);

    return url;
}

function sanitizeOfflineResource(resource) {
    const copy = { ...resource };

    delete copy.offline_file_url;
    delete copy.offline_available;

    if (typeof copy.cover_image === 'string' && copy.cover_image.startsWith('blob:')) {
        delete copy.cover_image;
    }

    return copy;
}

function enrichResource(resource, coverBlob, fileBlob) {
    const enriched = { ...resource, offline_available: true };

    if (coverBlob) {
        enriched.cover_image = rememberObjectUrl(coverKey(resource.slug), coverBlob);
    } else {
        delete enriched.cover_image;
    }

    if (fileBlob) {
        enriched.offline_file_url = rememberObjectUrl(fileKey(resource.slug), fileBlob);
        enriched.offline_file_name = resource.primary_file?.file_name ?? `${resource.slug}.bin`;
        enriched.offline_file_type = resource.primary_file?.file_type ?? 'bin';
    }

    return enriched;
}

export async function getOfflineResource(slug) {
    const index = loadMetaIndex();
    const entry = index[slug];

    if (! entry?.resource) {
        return null;
    }

    const [coverBlob, fileBlob] = await Promise.all([
        idbGet(coverKey(slug)),
        idbGet(fileKey(slug)),
    ]);

    return enrichResource(entry.resource, coverBlob, fileBlob);
}

export async function getAllOfflineResources() {
    const index = loadMetaIndex();
    const slugs = Object.keys(index);

    const resources = await Promise.all(slugs.map((slug) => getOfflineResource(slug)));

    return resources
        .filter(Boolean)
        .sort((a, b) => new Date(b.offline_downloaded_at ?? 0) - new Date(a.offline_downloaded_at ?? 0));
}

export async function getDownloadedSlugs() {
    return Object.keys(loadMetaIndex());
}

export async function isOfflineDownloaded(slug) {
    return Boolean(loadMetaIndex()[slug]);
}

export function repairOfflineStorage() {
    loadMetaIndex();
}

export async function saveOfflineResource(resource, { fileBlob = null, coverBlob = null } = {}) {
    if (! resource?.slug) {
        throw new Error('Resource slug is required for offline storage.');
    }

    const index = loadMetaIndex();
    const { offline_file_url: _fileUrl, offline_available: _offline, ...persistable } = sanitizeOfflineResource(resource);

    index[resource.slug] = {
        resource: {
            ...persistable,
            offline_downloaded_at: new Date().toISOString(),
        },
        downloadedAt: new Date().toISOString(),
    };

    saveMetaIndex(index);

    if (fileBlob) {
        await idbPut(fileKey(resource.slug), fileBlob);
    }

    if (coverBlob) {
        await idbPut(coverKey(resource.slug), coverBlob);
    }

    return getOfflineResource(resource.slug);
}

export async function removeOfflineResource(slug) {
    const index = loadMetaIndex();

    delete index[slug];
    saveMetaIndex(index);

    revokeObjectUrl(fileKey(slug));
    revokeObjectUrl(coverKey(slug));

    await Promise.all([
        idbDeleteByPrefix(`file:${slug}`),
        idbDeleteByPrefix(`cover:${slug}`),
    ]);
}

export async function mergeResourcesWithOffline(resources) {
    const offlineResources = await getAllOfflineResources();
    const bySlug = new Map((resources ?? []).map((resource) => [resource.slug, resource]));

    for (const offlineResource of offlineResources) {
        const existing = bySlug.get(offlineResource.slug) ?? {};
        const merged = { ...existing, ...offlineResource };

        if (! offlineResource.cover_image) {
            delete merged.cover_image;
        }

        bySlug.set(offlineResource.slug, merged);
    }

    return [...bySlug.values()];
}

export async function hydrateLegacyDownloadSlugs() {
    const index = loadMetaIndex();

    if (Object.keys(index).length > 0) {
        return Object.keys(index);
    }

    try {
        const stored = localStorage.getItem(LEGACY_DOWNLOADS_KEY);

        if (! stored) {
            return [];
        }

        const slugs = JSON.parse(stored);

        if (! Array.isArray(slugs)) {
            return [];
        }

        localStorage.removeItem(LEGACY_DOWNLOADS_KEY);

        return slugs.filter((slug) => typeof slug === 'string' && slug.length > 0);
    } catch {
        return [];
    }
}

export function resolveDownloadUrl(resource) {
    const primaryFile = resource?.primary_file;

    if (! primaryFile?.id) {
        return null;
    }

    if (primaryFile.download_url) {
        return primaryFile.download_url.replace(/^\/api\/v1/, '');
    }

    return `/resources/${resource.slug}/files/${primaryFile.id}/download`;
}

export async function fetchBlob(url, { signal, onProgress } = {}) {
    const baseUrl = (window.axios.defaults.baseURL || '').replace(/\/$/, '');
    const requestUrl = url.startsWith('http')
        ? url
        : `${baseUrl}${url.startsWith('/') ? url : `/${url}`}`;

    const response = await fetch(requestUrl, {
        signal,
        headers: {
            Accept: 'application/octet-stream,*/*',
            'X-Requested-With': 'XMLHttpRequest',
            'X-Device-Id': window.axios.defaults.headers.common['X-Device-Id'] ?? '',
            'X-Device-Name': window.axios.defaults.headers.common['X-Device-Name'] ?? '',
        },
    });

    if (! response.ok) {
        throw new Error(`Download failed (${response.status})`);
    }

    const blob = await response.blob();

    if (! blob.size) {
        throw new Error('Download failed (empty file)');
    }

    onProgress?.(100);

    return blob;
}

export async function fetchCoverBlob(coverUrl, options = {}) {
    if (! coverUrl || coverUrl.startsWith('data:') || coverUrl.startsWith('blob:')) {
        return null;
    }

    try {
        const absoluteUrl = coverUrl.startsWith('http')
            ? coverUrl
            : `${window.location.origin}${coverUrl.startsWith('/') ? coverUrl : `/${coverUrl}`}`;

        const response = await fetch(absoluteUrl, { signal: options.signal });

        if (! response.ok) {
            return null;
        }

        return await response.blob();
    } catch {
        return null;
    }
}
