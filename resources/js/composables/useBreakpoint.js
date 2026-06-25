import { computed, onMounted, onUnmounted, ref } from 'vue';

export function useBreakpoint() {
    const width = ref(typeof window !== 'undefined' ? window.innerWidth : 1024);

    function update() {
        width.value = window.innerWidth;
    }

    onMounted(() => window.addEventListener('resize', update));
    onUnmounted(() => window.removeEventListener('resize', update));

    const isMobile = computed(() => width.value < 768);
    const isTablet = computed(() => width.value >= 768 && width.value < 1280);
    const isDesktop = computed(() => width.value >= 1280);

    return { width, isMobile, isTablet, isDesktop };
}
