import { onBeforeUnmount, ref, shallowRef } from 'vue';

const DEFAULT_THRESHOLD = 72;
const DEFAULT_MAX_PULL = 120;

export function usePullToRefresh({
    onRefresh,
    threshold = DEFAULT_THRESHOLD,
    maxPull = DEFAULT_MAX_PULL,
    disabled = () => false,
} = {}) {
    const scrollEl = shallowRef(null);
    const pullDistance = ref(0);
    const isRefreshing = ref(false);
    const isPulling = ref(false);

    let startY = 0;
    let activeTouchId = null;

    function resetPull() {
        pullDistance.value = 0;
        isPulling.value = false;
        startY = 0;
        activeTouchId = null;
    }

    function onTouchStart(event) {
        if (disabled() || isRefreshing.value || event.touches.length !== 1) {
            return;
        }

        if ((scrollEl.value?.scrollTop ?? 0) > 0) {
            return;
        }

        activeTouchId = event.touches[0].identifier;
        startY = event.touches[0].clientY;
        isPulling.value = true;
    }

    function onTouchMove(event) {
        if (! isPulling.value || disabled() || isRefreshing.value) {
            return;
        }

        const touch = [...event.touches].find((item) => item.identifier === activeTouchId);

        if (! touch) {
            return;
        }

        if ((scrollEl.value?.scrollTop ?? 0) > 0) {
            resetPull();

            return;
        }

        const delta = touch.clientY - startY;

        if (delta <= 0) {
            pullDistance.value = 0;

            return;
        }

        event.preventDefault();
        pullDistance.value = Math.min(maxPull, delta * 0.45);
    }

    async function onTouchEnd() {
        if (! isPulling.value || disabled() || isRefreshing.value) {
            resetPull();

            return;
        }

        const shouldRefresh = pullDistance.value >= threshold;

        resetPull();

        if (! shouldRefresh || typeof onRefresh !== 'function') {
            return;
        }

        isRefreshing.value = true;

        try {
            await onRefresh();
        } finally {
            isRefreshing.value = false;
        }
    }

    function bindScrollElement(element) {
        if (scrollEl.value === element) {
            return () => {};
        }

        unbindScrollElement();

        scrollEl.value = element;

        element.addEventListener('touchstart', onTouchStart, { passive: true });
        element.addEventListener('touchmove', onTouchMove, { passive: false });
        element.addEventListener('touchend', onTouchEnd, { passive: true });
        element.addEventListener('touchcancel', onTouchEnd, { passive: true });

        return unbindScrollElement;
    }

    function unbindScrollElement() {
        const element = scrollEl.value;

        if (! element) {
            return;
        }

        element.removeEventListener('touchstart', onTouchStart);
        element.removeEventListener('touchmove', onTouchMove);
        element.removeEventListener('touchend', onTouchEnd);
        element.removeEventListener('touchcancel', onTouchEnd);
        scrollEl.value = null;
    }

    onBeforeUnmount(unbindScrollElement);

    return {
        scrollEl,
        pullDistance,
        isRefreshing,
        isPulling,
        threshold,
        bindScrollElement,
        unbindScrollElement,
    };
}
