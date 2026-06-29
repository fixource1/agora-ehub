import { readStoredJson } from '@/lib/appStorage';

export const PAGE_BOOKMARKS_KEY = 'agora-reader-page-bookmarks';
export const USER_NOTES_KEY = 'agora-reader-user-notes';

export function loadPageBookmarkIndex() {
    return readStoredJson(PAGE_BOOKMARKS_KEY, {});
}

export function loadUserNotesIndex() {
    return readStoredJson(USER_NOTES_KEY, {});
}

export function getPageBookmarkResourceSlugs() {
    const index = loadPageBookmarkIndex();

    return new Set(
        Object.entries(index)
            .filter(([, entries]) => Array.isArray(entries) && entries.length > 0)
            .map(([slug]) => slug),
    );
}

export function getUserNoteResourceSlugs() {
    const index = loadUserNotesIndex();

    return new Set(
        Object.entries(index)
            .filter(([, entries]) => Array.isArray(entries) && entries.length > 0)
            .map(([slug]) => slug),
    );
}

export function countAllPageBookmarks() {
    const index = loadPageBookmarkIndex();

    return Object.values(index).reduce((total, entries) => (
        total + (Array.isArray(entries) ? entries.length : 0)
    ), 0);
}

export function countAllUserNotes() {
    const index = loadUserNotesIndex();

    return Object.values(index).reduce((total, entries) => (
        total + (Array.isArray(entries) ? entries.length : 0)
    ), 0);
}
