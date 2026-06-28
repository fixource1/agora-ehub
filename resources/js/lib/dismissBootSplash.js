const MIN_SPLASH_MS = 600;

let bootStartedAt = performance.now();

export function markBootSplashStart() {
    bootStartedAt = performance.now();
}

export function dismissBootSplash() {
    const splash = document.getElementById('app-boot-splash');

    if (! splash || splash.dataset.dismissed === 'true') {
        return;
    }

    splash.dataset.dismissed = 'true';
    splash.classList.add('app-boot-splash--hidden');

    window.setTimeout(() => {
        splash.remove();
    }, 450);
}

export function dismissBootSplashWhenReady(readyPromise) {
    return readyPromise.then(() => {
        const elapsed = performance.now() - bootStartedAt;
        const wait = Math.max(0, MIN_SPLASH_MS - elapsed);

        window.setTimeout(dismissBootSplash, wait);
    });
}
