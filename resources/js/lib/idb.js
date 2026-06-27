const DB_NAME = 'agora-offline';
const DB_VERSION = 1;
const FILE_STORE = 'files';

let dbPromise = null;

function openDatabase() {
    if (dbPromise) {
        return dbPromise;
    }

    dbPromise = new Promise((resolve, reject) => {
        if (typeof indexedDB === 'undefined') {
            reject(new Error('IndexedDB is not available.'));
            return;
        }

        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onupgradeneeded = () => {
            const database = request.result;

            if (! database.objectStoreNames.contains(FILE_STORE)) {
                database.createObjectStore(FILE_STORE);
            }
        };

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error ?? new Error('Could not open offline database.'));
    });

    return dbPromise;
}

export async function idbGet(key) {
    const database = await openDatabase();

    return new Promise((resolve, reject) => {
        const transaction = database.transaction(FILE_STORE, 'readonly');
        const request = transaction.objectStore(FILE_STORE).get(key);

        request.onsuccess = () => resolve(request.result ?? null);
        request.onerror = () => reject(request.error ?? new Error('Could not read offline file.'));
    });
}

export async function idbPut(key, value) {
    const database = await openDatabase();

    return new Promise((resolve, reject) => {
        const transaction = database.transaction(FILE_STORE, 'readwrite');
        const request = transaction.objectStore(FILE_STORE).put(value, key);

        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error ?? new Error('Could not save offline file.'));
    });
}

export async function idbDelete(key) {
    const database = await openDatabase();

    return new Promise((resolve, reject) => {
        const transaction = database.transaction(FILE_STORE, 'readwrite');
        const request = transaction.objectStore(FILE_STORE).delete(key);

        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error ?? new Error('Could not delete offline file.'));
    });
}

export async function idbDeleteByPrefix(prefix) {
    const database = await openDatabase();

    return new Promise((resolve, reject) => {
        const transaction = database.transaction(FILE_STORE, 'readwrite');
        const store = transaction.objectStore(FILE_STORE);
        const request = store.openCursor();
        const keysToDelete = [];

        request.onsuccess = () => {
            const cursor = request.result;

            if (! cursor) {
                for (const key of keysToDelete) {
                    store.delete(key);
                }

                resolve();

                return;
            }

            if (String(cursor.key).startsWith(prefix)) {
                keysToDelete.push(cursor.key);
            }

            cursor.continue();
        };

        request.onerror = () => reject(request.error ?? new Error('Could not delete offline files.'));
    });
}
