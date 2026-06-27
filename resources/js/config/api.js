/**
 * Mobile API configuration injected from Laravel (see resources/views/app.blade.php).
 */
export function getAgoraConfig() {
    return window.__AGORA__ ?? {};
}

export function isNativePlatform() {
    const platform = getAgoraConfig().nativePlatform;

    if (platform === 'android' || platform === 'ios') {
        return true;
    }

    return typeof window !== 'undefined'
        && (window.NativePHP !== undefined || navigator.userAgent.includes('NativePHP'));
}

export function isRemoteMobileApi() {
    const config = getAgoraConfig();

    return Boolean(config.mobileApiEnabled && config.mobileApiBaseUrl);
}

export function isEmbeddedMobileApi() {
    return isNativePlatform() && ! isRemoteMobileApi();
}

export function resolveApiBaseUrl() {
    const config = getAgoraConfig();

    if (isRemoteMobileApi()) {
        return `${String(config.mobileApiBaseUrl).replace(/\/$/, '')}/api/v1`;
    }

    if (isEmbeddedMobileApi() && typeof window !== 'undefined') {
        return `${window.location.origin}/api/v1`;
    }

    return '/api/v1';
}
