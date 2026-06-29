const STORAGE_KEY = 'agora-reader-preferences';

const defaults = {
    readingMode: 'vertical',
};

function loadPreferences() {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);

        if (! stored) {
            return { ...defaults };
        }

        const parsed = JSON.parse(stored);

        return {
            readingMode: parsed?.readingMode === 'horizontal' ? 'horizontal' : 'vertical',
        };
    } catch {
        return { ...defaults };
    }
}

function savePreferences(prefs) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify({
        readingMode: prefs.readingMode,
    }));
}

export function useReaderPreferences() {
    const stored = loadPreferences();

    return {
        initialReadingMode: stored.readingMode,
        persist(preferences) {
            savePreferences(preferences);
        },
    };
}
