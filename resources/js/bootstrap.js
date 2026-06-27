import axios from 'axios';
import { isNativePlatform, isRemoteMobileApi, resolveApiBaseUrl } from './config/api';
import { initTouchPressFeedback } from './lib/touchPress';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

function getDeviceId() {
    const storageKey = 'agora-device-id';
    let deviceId = localStorage.getItem(storageKey);

    if (! deviceId) {
        deviceId = crypto.randomUUID();
        localStorage.setItem(storageKey, deviceId);
    }

    return deviceId;
}

const isNative = isNativePlatform();
const platform = isNative
    ? (navigator.userAgent.includes('Android') ? 'android' : 'ios')
    : 'web';

window.axios.defaults.baseURL = resolveApiBaseUrl();
window.axios.defaults.headers.common['X-Device-Id'] = getDeviceId();
window.axios.defaults.headers.common['X-Device-Name'] = isNative ? platform : 'Web';

if (isRemoteMobileApi()) {
    window.axios.defaults.timeout = 15000;
}

if (isNative) {
    document.documentElement.classList.add('native-app');
    initTouchPressFeedback();

    document.addEventListener('contextmenu', (event) => {
        const target = event.target instanceof Element
            ? event.target.closest('a, button, [role="button"], .tap-feedback')
            : null;

        if (target) {
            event.preventDefault();
        }
    }, { capture: true });

    document.addEventListener('selectstart', (event) => {
        const target = event.target instanceof Element
            ? event.target.closest('a, button, .tap-feedback, .library-nav-item, .app-bottom-nav-item')
            : null;

        if (target) {
            event.preventDefault();
        }
    }, { capture: true });
}
