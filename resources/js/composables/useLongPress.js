/**
 * @param {() => void} onLongPress
 * @param {{ delay?: number }} [options]
 */
export function useLongPress(onLongPress, options = {}) {
    const delay = options.delay ?? 450;
    let timer = null;
    let triggered = false;

    function cancel() {
        if (timer) {
            clearTimeout(timer);
            timer = null;
        }
    }

    function onPointerDown(event) {
        if (event.pointerType === 'mouse' && event.button !== 0) {
            return;
        }

        triggered = false;
        cancel();

        timer = window.setTimeout(() => {
            triggered = true;

            if (navigator.vibrate) {
                navigator.vibrate(12);
            }

            onLongPress(event);
        }, delay);
    }

    function onPointerUp() {
        cancel();
    }

    function onPointerLeave() {
        cancel();
    }

    function onPointerCancel() {
        cancel();
    }

    function onPointerMove() {
        cancel();
    }

    function onClick(event) {
        if (! triggered) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();
        triggered = false;
    }

    return {
        onPointerdown: onPointerDown,
        onPointerup: onPointerUp,
        onPointerleave: onPointerLeave,
        onPointercancel: onPointerCancel,
        onPointermove: onPointerMove,
        onClick,
    };
}
