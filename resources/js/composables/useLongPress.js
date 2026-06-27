import { usePressable } from '@/composables/usePressable';

/**
 * @param {() => void} onLongPress
 * @param {{ delay?: number }} [options]
 */
export function useLongPress(onLongPress, options = {}) {
    return usePressable({
        onLongPress,
        longPressDelay: options.delay,
    });
}
