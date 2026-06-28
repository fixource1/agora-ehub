import { reactive } from 'vue';
import { getOfflineResource } from '@/composables/useOfflineStore';

const cache = reactive(new Map());
const inflight = new Map();

function setResource(slug, resource) {
    if (! slug || ! resource) {
        return;
    }

    cache.set(slug, resource);
}

function getResource(slug) {
    return cache.get(slug) ?? null;
}

function seedFromList(resources) {
    for (const resource of resources ?? []) {
        if (! cache.has(resource.slug)) {
            cache.set(resource.slug, resource);
        }
    }
}

function hasDetailData(resource) {
    return resource != null && Object.prototype.hasOwnProperty.call(resource, 'description');
}

async function fetchResource(slug) {
    if (! slug) {
        return null;
    }

    const cached = cache.get(slug);

    if (cached && hasDetailData(cached)) {
        return cached;
    }

    if (inflight.has(slug)) {
        return inflight.get(slug);
    }

    const request = window.axios
        .get(`/resources/${slug}`)
        .then((response) => {
            const resource = response.data.data;
            setResource(slug, resource);

            return resource;
        })
        .catch(async () => {
            const offline = await getOfflineResource(slug);

            if (offline) {
                setResource(slug, offline);

                return offline;
            }

            throw new Error('Could not load resource.');
        })
        .finally(() => {
            inflight.delete(slug);
        });

    inflight.set(slug, request);

    return request;
}

function prefetchResource(slug) {
    if (! slug || inflight.has(slug)) {
        return;
    }

    const cached = cache.get(slug);

    if (cached && hasDetailData(cached)) {
        return;
    }

    fetchResource(slug).catch(() => {});
}

export function useResourceCache() {
    return {
        getResource,
        setResource,
        seedFromList,
        fetchResource,
        prefetchResource,
        hasDetailData,
    };
}
