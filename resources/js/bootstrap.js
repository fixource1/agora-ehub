import axios from 'axios';

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

window.axios.defaults.headers.common['X-Device-Id'] = getDeviceId();
window.axios.defaults.headers.common['X-Device-Name'] = 'Web';
