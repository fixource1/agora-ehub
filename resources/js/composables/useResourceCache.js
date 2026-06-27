import { reactive } from 'vue';

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
    return Array.isArray(resource?.authors) || resource?.metadata != null;
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
        .get(`/api/v1/resources/${slug}`)
        .then((response) => {
            const resource = response.data.data;
            setResource(slug, resource);

            return resource;
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
