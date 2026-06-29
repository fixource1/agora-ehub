import { computed, ref, watch } from 'vue';
import { writeStoredJson } from '@/lib/appStorage';
import { useLibrary } from '@/composables/useLibrary';
import { loadPageBookmarkIndex, PAGE_BOOKMARKS_KEY } from '@/lib/readerStorage';

function normalizeEntry(entry) {
    return {
        id: entry.id,
        page: Number(entry.page),
        label: entry.label ?? `Page ${entry.page}`,
        createdAt: entry.createdAt ?? Date.now(),
    };
}

export function useReaderPageBookmarks(slugRef) {
    const library = useLibrary();
    const bookmarks = ref([]);

    function syncFromStorage() {
        const slug = slugRef.value;

        if (! slug) {
            bookmarks.value = [];
            return;
        }

        const index = loadPageBookmarkIndex();
        const entries = Array.isArray(index[slug]) ? index[slug] : [];

        bookmarks.value = entries
            .map(normalizeEntry)
            .filter((entry) => entry.page > 0)
            .sort((a, b) => a.page - b.page || a.createdAt - b.createdAt);
    }

    function persist() {
        const slug = slugRef.value;

        if (! slug) {
            return false;
        }

        const index = loadPageBookmarkIndex();
        index[slug] = bookmarks.value.map((entry) => ({ ...entry }));
        writeStoredJson(PAGE_BOOKMARKS_KEY, index);

        return true;
    }

    function addBookmark(page, label = null) {
        const pageNumber = Number(page);

        if (! pageNumber || pageNumber < 1) {
            return false;
        }

        const existing = bookmarks.value.find((entry) => entry.page === pageNumber);

        if (existing) {
            return true;
        }

        bookmarks.value = [
            ...bookmarks.value,
            normalizeEntry({
                id: `${Date.now()}-${pageNumber}`,
                page: pageNumber,
                label: label ?? `Page ${pageNumber}`,
                createdAt: Date.now(),
            }),
        ].sort((a, b) => a.page - b.page || a.createdAt - b.createdAt);

        if (! persist()) {
            return false;
        }

        library.markResourceBookmarked(slugRef.value);

        return true;
    }

    function removeBookmark(id) {
        bookmarks.value = bookmarks.value.filter((entry) => entry.id !== id);
        persist();
        library.refreshReaderCounts();
    }

    function toggleBookmark(page, label = null) {
        const pageNumber = Number(page);
        const existing = bookmarks.value.find((entry) => entry.page === pageNumber);

        if (existing) {
            removeBookmark(existing.id);
            return false;
        }

        return addBookmark(pageNumber, label);
    }

    function isPageBookmarked(page) {
        return bookmarks.value.some((entry) => entry.page === Number(page));
    }

    watch(slugRef, syncFromStorage, { immediate: true });

    const bookmarkCount = computed(() => bookmarks.value.length);

    return {
        bookmarks,
        bookmarkCount,
        addBookmark,
        removeBookmark,
        toggleBookmark,
        isPageBookmarked,
        refresh: syncFromStorage,
    };
}
