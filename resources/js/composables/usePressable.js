import { computed, ref } from 'vue';

const MOVE_THRESHOLD_PX = 10;

/**
 * Lightweight long-press helper. Short taps use CSS :active via .tap-feedback.
 *
 * @param {{
 *   onLongPress?: (event: Event) => void,
 *   longPressDelay?: number,
 *   hapticOnLongPress?: boolean,
 * }} [options]
 */
export function usePressable(options = {}) {
    const {
        onLongPress,
        longPressDelay = 400,
        hapticOnLongPress = true,
    } = options;

    const isLongPressing = ref(false);

    let timer = null;
    let longPressTriggered = false;
    let startX = 0;
    let startY = 0;

    function clearTimer() {
        if (timer) {
            clearTimeout(timer);
            timer = null;
        }
    }

    function resetLongPress() {
        isLongPressing.value = false;
        longPressTriggered = false;
    }

    function cancelPress() {
        clearTimer();
        resetLongPress();
    }

    function beginPress(event, clientX, clientY) {
        if (! onLongPress) {
            return;
        }

        longPressTriggered = false;
        startX = clientX;
        startY = clientY;
        clearTimer();

        timer = window.setTimeout(() => {
            longPressTriggered = true;
            isLongPressing.value = true;

            if (hapticOnLongPress && navigator.vibrate) {
                navigator.vibrate(10);
            }

            onLongPress(event);
        }, longPressDelay);
    }

    function endPress() {
        clearTimer();

        if (! longPressTriggered) {
            resetLongPress();
        }
    }

    function onPointerDown(event) {
        if (event.pointerType === 'mouse' && event.button !== 0) {
            return;
        }

        beginPress(event, event.clientX, event.clientY);
    }

    function onPointerUp() {
        endPress();

        if (longPressTriggered) {
            requestAnimationFrame(resetLongPress);
        }
    }

    function onPointerCancel() {
        cancelPress();
    }

    function onPointerMove(event) {
        const deltaX = Math.abs(event.clientX - startX);
        const deltaY = Math.abs(event.clientY - startY);

        if (deltaX > MOVE_THRESHOLD_PX || deltaY > MOVE_THRESHOLD_PX) {
            cancelPress();
        }
    }

    function onClick(event) {
        if (! longPressTriggered) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();
        resetLongPress();
    }

    const pressClass = computed(() => ({
        'tap-feedback': Boolean(onLongPress),
        'tap-feedback--long-pressing': isLongPressing.value,
    }));

    const handlers = onLongPress
        ? {
            onPointerdown: onPointerDown,
            onPointerup: onPointerUp,
            onPointercancel: onPointerCancel,
            onPointermove: onPointerMove,
            onClick,
        }
        : {};

    return {
        isLongPressing,
        pressClass,
        handlers,
    };
}
