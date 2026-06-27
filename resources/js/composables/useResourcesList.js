import { computed, reactive } from 'vue';
import { useResourceCache } from '@/composables/useResourceCache';

const state = reactive({
    resources: [],
    loading: false,
    error: null,
    loaded: false,
});

let inflight = null;

export function useResourcesList() {
    const { seedFromList } = useResourceCache();

    async function load(options = {}) {
        const { force = false } = options;

        if (state.loaded && ! force) {
            return state.resources;
        }

        if (inflight) {
            return inflight;
        }

        state.loading = true;
        state.error = null;

        inflight = window.axios
            .get('/api/v1/resources')
            .then((response) => {
                state.resources = response.data.data ?? [];
                seedFromList(state.resources);
                state.loaded = true;

                return state.resources;
            })
            .catch(() => {
                state.error = 'Could not load resources. Check your connection and try again.';

                if (! state.loaded) {
                    state.resources = [];
                }

                return state.resources;
            })
            .finally(() => {
                state.loading = false;
                inflight = null;
            });

        return inflight;
    }

    function retry() {
        state.loaded = false;

        return load({ force: true });
    }

    return {
        resources: computed(() => state.resources),
        loading: computed(() => state.loading),
        error: computed(() => state.error),
        loaded: computed(() => state.loaded),
        load,
        retry,
    };
}
