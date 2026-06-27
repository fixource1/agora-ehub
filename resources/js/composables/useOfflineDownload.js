import { useLibrary } from '@/composables/useLibrary';
import { useResourceCache } from '@/composables/useResourceCache';
import {
    fetchBlob,
    fetchCoverBlob,
    getOfflineResource,
    removeOfflineResource,
    resolveDownloadUrl,
    saveOfflineResource,
} from '@/composables/useOfflineStore';

export function useOfflineDownload() {
    const library = useLibrary();
    const { fetchResource, hasDetailData, setResource } = useResourceCache();

    async function ensureResourceDetail(resource) {
        if (hasDetailData(resource)) {
            return resource;
        }

        return fetchResource(resource.slug);
    }

    async function downloadResource(resource, { onProgress, signal } = {}) {
        const detailed = await ensureResourceDetail(resource);
        const downloadUrl = resolveDownloadUrl(detailed);

        if (! downloadUrl) {
            throw new Error('This resource does not have a downloadable file.');
        }

        const [fileBlob, coverBlob] = await Promise.all([
            fetchBlob(downloadUrl, {
                signal,
                onProgress: (progress) => onProgress?.(Math.min(progress, 95)),
            }),
            fetchCoverBlob(detailed.cover_image, { signal }),
        ]);

        const saved = await saveOfflineResource(detailed, { fileBlob, coverBlob });
        setResource(detailed.slug, saved);
        library.markDownloaded(detailed.slug);

        try {
            await window.axios.post(
                '/downloads',
                { resource_id: detailed.id, resource_file_id: detailed.primary_file?.id ?? null },
                { signal },
            );
        } catch {
            // Local offline copy is the source of truth on device.
        }

        onProgress?.(100);

        return saved;
    }

    async function removeDownload(slug) {
        library.unmarkDownloaded(slug);
        await removeOfflineResource(slug);

        try {
            await window.axios.delete(`/downloads/${slug}`);
        } catch {
            // Local removal succeeded; server sync is optional when offline.
        }
    }

    async function getDownloadedResource(slug) {
        if (! library.isDownloaded(slug)) {
            return null;
        }

        return getOfflineResource(slug);
    }

    return {
        downloadResource,
        removeDownload,
        getDownloadedResource,
    };
}
