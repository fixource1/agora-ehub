import { computed, reactive, ref } from 'vue';
import { mergeResourcesWithOffline } from '@/composables/useOfflineStore';
import { useResourceCache } from '@/composables/useResourceCache';

const state = reactive({
    resources: [],
    loading: false,
    loadingMore: false,
    error: null,
    loaded: false,
    nextCursor: null,
    hasMore: false,
    total: null,
});

const queryParams = ref({});

let inflight = null;

function buildParams(extra = {}) {
    return {
        per_page: 24,
        ...queryParams.value,
        ...extra,
    };
}

function paramsEqual(left, right) {
    return JSON.stringify(left ?? {}) === JSON.stringify(right ?? {});
}

export function useResourcesList() {
    const { seedFromList } = useResourceCache();

    async function fetchPage(params, { append = false } = {}) {
        const response = await window.axios.get('/resources', { params });
        const page = response.data.data ?? [];
        const resources = await mergeResourcesWithOffline(page);

        if (append) {
            const existing = new Set(state.resources.map((resource) => resource.slug));
            state.resources = [
                ...state.resources,
                ...resources.filter((resource) => ! existing.has(resource.slug)),
            ];
        } else {
            state.resources = resources;
        }

        seedFromList(state.resources);

        const meta = response.data.meta ?? {};
        state.nextCursor = meta.next_cursor ?? null;
        state.hasMore = Boolean(state.nextCursor);
        state.total = meta.total ?? state.resources.length;
        state.loaded = true;
        state.error = null;

        return state.resources;
    }

    async function load(options = {}) {
        const { force = false, params = {}, append = false } = options;

        if (Object.keys(params).length > 0) {
            queryParams.value = { ...queryParams.value, ...params };
        }

        if (state.loaded && ! force && ! append) {
            return state.resources;
        }

        if (inflight) {
            return inflight;
        }

        state.loading = ! append;
        state.error = null;

        inflight = fetchPage(buildParams(append ? { cursor: state.nextCursor } : {}), {
            append,
        })
            .catch(async () => {
                const offlineResources = await mergeResourcesWithOffline(state.resources);

                if (offlineResources.length > 0) {
                    state.resources = offlineResources;
                    seedFromList(state.resources);
                    state.loaded = true;
                    state.error = null;
                } else {
                    state.error = 'Could not load resources. Check your connection and try again.';

                    if (! state.loaded) {
                        state.resources = [];
                    }
                }

                return state.resources;
            })
            .finally(() => {
                state.loading = false;
                state.loadingMore = false;
                inflight = null;
            });

        return inflight;
    }

    function setFilters(params, { force = false } = {}) {
        const next = { ...params };

        if (! force && state.loaded && paramsEqual(queryParams.value, next)) {
            return Promise.resolve(state.resources);
        }

        queryParams.value = next;
        state.loaded = false;
        state.nextCursor = null;
        state.hasMore = false;

        return load({ force: true });
    }

    function refresh() {
        state.nextCursor = null;
        state.hasMore = false;

        return load({ force: true });
    }

    function loadMore() {
        if (! state.hasMore || state.loadingMore || state.loading) {
            return Promise.resolve(state.resources);
        }

        state.loadingMore = true;

        return load({ force: true, append: true });
    }

    function retry() {
        state.loaded = false;

        return load({ force: true });
    }

    return {
        resources: computed(() => state.resources),
        loading: computed(() => state.loading),
        loadingMore: computed(() => state.loadingMore),
        error: computed(() => state.error),
        loaded: computed(() => state.loaded),
        hasMore: computed(() => state.hasMore),
        total: computed(() => state.total),
        queryParams,
        load,
        loadMore,
        setFilters,
        refresh,
        retry,
    };
}
