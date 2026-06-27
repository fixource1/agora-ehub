import { onUnmounted, ref, watch } from 'vue';

export function useDelayedLoading(isLoading, delayMs = 200) {
    const showSkeleton = ref(false);
    let timer = null;

    watch(
        isLoading,
        (loading) => {
            clearTimeout(timer);

            if (loading) {
                timer = setTimeout(() => {
                    showSkeleton.value = true;
                }, delayMs);
            } else {
                showSkeleton.value = false;
            }
        },
        { immediate: true },
    );

    onUnmounted(() => {
        clearTimeout(timer);
    });

    return { showSkeleton };
}
