const PRESS_SELECTOR = [
    '.tap-feedback',
    '.library-nav-item',
    '.library-collections-add',
    '.collection-modal-button',
    '.action-menu__item',
    '.app-bottom-nav-item',
].join(',');

const IGNORE_SELECTOR = 'input, textarea, select, [contenteditable="true"], [data-no-pressable]';

let activeElement = null;

function findPressable(target) {
    if (!(target instanceof Element)) {
        return null;
    }

    if (target.closest(IGNORE_SELECTOR)) {
        return null;
    }

    return target.closest(PRESS_SELECTOR);
}

function releasePress() {
    if (! activeElement) {
        return;
    }

    activeElement.classList.remove('is-touch-pressed');
    activeElement = null;
}

function onTouchStart(event) {
    if (event.touches.length !== 1) {
        return;
    }

    const element = findPressable(event.target);

    if (! element || element.disabled) {
        return;
    }

    releasePress();
    activeElement = element;
    element.classList.add('is-touch-pressed');
}

export function initTouchPressFeedback() {
    document.addEventListener('touchstart', onTouchStart, { capture: true, passive: true });
    document.addEventListener('touchend', releasePress, { capture: true, passive: true });
    document.addEventListener('touchcancel', releasePress, { capture: true, passive: true });
}
