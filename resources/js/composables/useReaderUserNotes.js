import { computed, ref, watch } from 'vue';
import { writeStoredJson } from '@/lib/appStorage';
import { useLibrary } from '@/composables/useLibrary';
import { loadUserNotesIndex, USER_NOTES_KEY } from '@/lib/readerStorage';

function normalizeEntry(entry) {
    return {
        id: entry.id,
        page: Number(entry.page),
        text: String(entry.text ?? '').trim(),
        createdAt: entry.createdAt ?? Date.now(),
        updatedAt: entry.updatedAt ?? entry.createdAt ?? Date.now(),
    };
}

export function useReaderUserNotes(slugRef) {
    const library = useLibrary();
    const notes = ref([]);

    function syncFromStorage() {
        const slug = slugRef.value;

        if (! slug) {
            notes.value = [];
            return;
        }

        const index = loadUserNotesIndex();
        const entries = Array.isArray(index[slug]) ? index[slug] : [];

        notes.value = entries
            .map(normalizeEntry)
            .filter((entry) => entry.page > 0 && entry.text)
            .sort((a, b) => a.page - b.page || b.updatedAt - a.updatedAt);
    }

    function persist() {
        const slug = slugRef.value;

        if (! slug) {
            return false;
        }

        const index = loadUserNotesIndex();
        index[slug] = notes.value.map((entry) => ({ ...entry }));
        writeStoredJson(USER_NOTES_KEY, index);

        return true;
    }

    function addNote(page, text) {
        const pageNumber = Number(page);
        const trimmed = String(text ?? '').trim();

        if (! pageNumber || pageNumber < 1 || ! trimmed) {
            return null;
        }

        const entry = normalizeEntry({
            id: `${Date.now()}-${pageNumber}`,
            page: pageNumber,
            text: trimmed,
            createdAt: Date.now(),
            updatedAt: Date.now(),
        });

        notes.value = [...notes.value, entry]
            .sort((a, b) => a.page - b.page || b.updatedAt - a.updatedAt);

        if (! persist()) {
            return null;
        }

        library.refreshReaderCounts();

        return entry;
    }

    function removeNote(id) {
        notes.value = notes.value.filter((entry) => entry.id !== id);
        persist();
        library.refreshReaderCounts();
    }

    function notesForPage(page) {
        const pageNumber = Number(page);

        return notes.value.filter((entry) => entry.page === pageNumber);
    }

    watch(slugRef, syncFromStorage, { immediate: true });

    const noteCount = computed(() => notes.value.length);

    return {
        notes,
        noteCount,
        addNote,
        removeNote,
        notesForPage,
        refresh: syncFromStorage,
    };
}
