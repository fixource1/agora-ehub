export function useNative() {
    const isNative = typeof window !== 'undefined'
        && (window.NativePHP !== undefined || navigator.userAgent.includes('NativePHP'));

    const platform = isNative
        ? (navigator.userAgent.includes('Android') ? 'android' : 'ios')
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

    return { isNative, platform, saveOffline, loadOffline };
}
