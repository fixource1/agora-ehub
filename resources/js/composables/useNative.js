import {
    isEmbeddedMobileApi,
    isNativePlatform,
    isRemoteMobileApi,
    resolveApiBaseUrl,
} from '@/config/api';

export function useNative() {
    const isNative = isNativePlatform();
    const platform = isNative
        ? (getAgoraConfigPlatform() === 'ios' ? 'ios' : 'android')
        : 'web';

    async function saveOffline(key, data) {
        if (isNative && window.NativePHP?.SecureStorage) {
            await window.NativePHP.SecureStorage.set(key, JSON.stringify(data));
            return;
        }
        localStorage.setItem(`agora:${key}`, JSON.stringify(data));
    }

    async function loadOffline(key) {
        if (isNative && window.NativePHP?.SecureStorage) {
            const value = await window.NativePHP.SecureStorage.get(key);
            return value ? JSON.parse(value) : null;
        }
        const raw = localStorage.getItem(`agora:${key}`);
        return raw ? JSON.parse(raw) : null;
    }

    return {
        isNative,
        platform,
        isRemoteApi: isRemoteMobileApi(),
        isEmbeddedApi: isEmbeddedMobileApi(),
        apiBaseUrl: resolveApiBaseUrl(),
        saveOffline,
        loadOffline,
    };
}

function getAgoraConfigPlatform() {
    const configured = window.__AGORA__?.nativePlatform;

    if (configured === 'android' || configured === 'ios') {
        return configured;
    }

    return navigator.userAgent.includes('Android') ? 'android' : 'ios';
}
