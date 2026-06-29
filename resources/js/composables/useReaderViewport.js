import { onBeforeUnmount, onMounted, ref } from 'vue';

function detectPortrait() {
    if (typeof window === 'undefined') {
        return false;
    }

    if (window.matchMedia('(orientation: portrait)').matches) {
        return true;
    }

    return window.innerHeight >= window.innerWidth;
}

export function useReaderViewport() {
    const isPortrait = ref(detectPortrait());

    function refreshPortraitState() {
        isPortrait.value = detectPortrait();
    }

    onMounted(() => {
        window.addEventListener('resize', refreshPortraitState);
        window.addEventListener('orientationchange', onOrientationChange);
        refreshPortraitState();
    });

    onBeforeUnmount(() => {
        window.removeEventListener('resize', refreshPortraitState);
        window.removeEventListener('orientationchange', onOrientationChange);
        window.clearTimeout(orientationTimer);
    });

    let orientationTimer = null;

    function onOrientationChange() {
        window.clearTimeout(orientationTimer);
        orientationTimer = window.setTimeout(refreshPortraitState, 150);
    }

    function resolveReadingMode(preferredMode) {
        if (isPortrait.value) {
            return 'vertical';
        }

        return preferredMode === 'horizontal' ? 'horizontal' : 'vertical';
    }

    return {
        isPortrait,
        resolveReadingMode,
        refreshPortraitState,
    };
}
