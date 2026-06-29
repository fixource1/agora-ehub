import { isNativePlatform } from '@/config/api';
import { PAGE_BOOKMARKS_KEY, USER_NOTES_KEY } from '@/lib/readerStorage';

export const BOOKMARKS_STORAGE_KEY = 'agora-bookmark-slugs';
export const COLLECTIONS_STORAGE_KEY = 'agora-collection-members';
export const COLLECTIONS_LIST_STORAGE_KEY = 'agora-collections';

export const APP_STORAGE_KEYS = [
    PAGE_BOOKMARKS_KEY,
    USER_NOTES_KEY,
    BOOKMARKS_STORAGE_KEY,
    COLLECTIONS_STORAGE_KEY,
    COLLECTIONS_LIST_STORAGE_KEY,
];

const memory = new Map();
let hydratePromise = null;

function cloneJson(value) {
    return JSON.parse(JSON.stringify(value));
}

function readFromLocalStorage(key) {
    try {
        const stored = localStorage.getItem(key);

        if (! stored) {
            return null;
        }

        return JSON.parse(stored);
    } catch {
        return null;
    }
}

function writeToLocalStorage(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));

        return true;
    } catch {
        return false;
    }
}

async function readFromNativeStorage(key) {
    if (! window.NativePHP?.SecureStorage) {
        return null;
    }

    try {
        const stored = await window.NativePHP.SecureStorage.get(key);

        if (! stored) {
            return null;
        }

        return JSON.parse(stored);
    } catch {
        return null;
    }
}

async function writeToNativeStorage(key, value) {
    if (! window.NativePHP?.SecureStorage) {
        return false;
    }

    try {
        await window.NativePHP.SecureStorage.set(key, JSON.stringify(value));

        return true;
    } catch {
        return false;
    }
}

async function hydrateKey(key, { force = false } = {}) {
    if (! force && memory.has(key)) {
        return;
    }

    let value = null;

    if (isNativePlatform()) {
        value = await readFromNativeStorage(key);
    }

    if (value == null) {
        value = readFromLocalStorage(key);
    }

    if (value == null) {
        memory.delete(key);

        return;
    }

    memory.set(key, value);

    if (isNativePlatform()) {
        await writeToNativeStorage(key, value);
        writeToLocalStorage(key, value);
    }
}

export function hydrateAppStorage(keys = APP_STORAGE_KEYS) {
    if (! hydratePromise) {
        hydratePromise = Promise.all(keys.map((key) => hydrateKey(key)));
    }

    return hydratePromise;
}

export function reloadAppStorage(keys = APP_STORAGE_KEYS) {
    return Promise.all(keys.map((key) => hydrateKey(key, { force: true })));
}

export function readStoredJson(key, fallback) {
    if (memory.has(key)) {
        return memory.get(key);
    }

    const localValue = readFromLocalStorage(key);

    if (localValue != null) {
        memory.set(key, localValue);

        return localValue;
    }

    return fallback;
}

export function writeStoredJson(key, value) {
    const payload = cloneJson(value);
    memory.set(key, payload);

    if (isNativePlatform()) {
        writeToNativeStorage(key, payload)
            .then((saved) => {
                if (! saved) {
                    writeToLocalStorage(key, payload);
                }
            })
            .catch(() => {
                writeToLocalStorage(key, payload);
            });

        writeToLocalStorage(key, payload);

        return;
    }

    writeToLocalStorage(key, payload);
}

export async function reloadStoredJson(key, fallback) {
    await hydrateKey(key);

    return readStoredJson(key, fallback);
}
