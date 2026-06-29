import { reactive } from 'vue';
import { getDownloadedSlugs, hydrateLegacyDownloadSlugs } from '@/composables/useOfflineStore';
import {
    BOOKMARKS_STORAGE_KEY,
    COLLECTIONS_LIST_STORAGE_KEY,
    COLLECTIONS_STORAGE_KEY,
    readStoredJson,
    writeStoredJson,
} from '@/lib/appStorage';
import {
    getPageBookmarkResourceSlugs,
    getUserNoteResourceSlugs,
} from '@/lib/readerStorage';

const COLLECTION_SECTION_PREFIX = 'collection:';

const DEFAULT_COLLECTIONS = [
    { id: 1, name: 'DOST GIA & Grants', count: 0 },
    { id: 2, name: 'REPS Guidelines', count: 0 },
    { id: 3, name: 'OVCRE Training', count: 0 },
    { id: 4, name: 'Research Ethics', count: 0 },
];

const DEFAULT_BOOKMARKS = [
    'dost-gia-guidelines-and-procedures',
    'reps-guidelines-handbook',
    'research-proposal-writing-for-ovcre',
    'reps-performance-evaluation-toolkit',
];

const DEFAULT_COLLECTION_MEMBERS = {
    1: ['dost-gia-guidelines-and-procedures', 'reps-guidelines-handbook', 'dost-gia-monitoring-and-evaluation-manual'],
    2: ['reps-guidelines-handbook', 'reps-performance-evaluation-toolkit'],
    3: ['research-proposal-writing-for-ovcre', 'technology-transfer-and-commercialization-primer', 'introduction-to-uplb-ovcre-services'],
    4: ['uplb-research-ethics-review-protocol'],
};

function loadBookmarkSlugs() {
    const slugs = readStoredJson(BOOKMARKS_STORAGE_KEY, null);

    if (Array.isArray(slugs)) {
        return new Set(slugs);
    }

    return new Set(DEFAULT_BOOKMARKS);
}

function loadCollectionMembers() {
    const parsed = readStoredJson(COLLECTIONS_STORAGE_KEY, null);

    if (parsed && typeof parsed === 'object') {
        const members = {};

        for (const [collectionId, slugs] of Object.entries(parsed)) {
            if (Array.isArray(slugs)) {
                members[collectionId] = new Set(slugs);
            }
        }

        return members;
    }

    return Object.fromEntries(
        Object.entries(DEFAULT_COLLECTION_MEMBERS).map(([id, slugs]) => [String(id), new Set(slugs)]),
    );
}

function persistBookmarkSlugs(slugs) {
    writeStoredJson(BOOKMARKS_STORAGE_KEY, [...slugs]);
}

function loadCollections() {
    const parsed = readStoredJson(COLLECTIONS_LIST_STORAGE_KEY, null);

    if (Array.isArray(parsed) && parsed.length > 0) {
        return parsed.map((collection) => ({
            id: collection.id,
            name: collection.name,
            count: 0,
        }));
    }

    return DEFAULT_COLLECTIONS.map((collection) => ({ ...collection }));
}

function persistCollections(collections) {
    writeStoredJson(
        COLLECTIONS_LIST_STORAGE_KEY,
        collections.map(({ id, name }) => ({ id, name })),
    );
}

function persistCollectionMembers(members) {
    writeStoredJson(
        COLLECTIONS_STORAGE_KEY,
        Object.fromEntries(
            Object.entries(members).map(([id, slugs]) => [id, [...slugs]]),
        ),
    );
}

const state = reactive({
    drawerOpen: false,
    activeSection: 'offline',
    counts: {
        offline: 0,
        allResources: 0,
        notes: 0,
        bookmarks: 0,
    },
    collections: loadCollections(),
    downloadedSlugs: new Set(),
    bookmarkSlugs: loadBookmarkSlugs(),
    collectionMembers: loadCollectionMembers(),
    noteSlugs: new Set(),
    recentSlugs: [
        'introduction-to-uplb-ovcre-services',
        'technology-transfer-and-commercialization-primer',
        'dost-gia-guidelines-and-procedures',
        'research-proposal-writing-for-ovcre',
    ],
});

function syncReaderDerivedCounts() {
    const pageBookmarkSlugs = getPageBookmarkResourceSlugs();
    const noteSlugs = getUserNoteResourceSlugs();

    state.noteSlugs = noteSlugs;
    state.counts.notes = noteSlugs.size;
    state.counts.bookmarks = new Set([...state.bookmarkSlugs, ...pageBookmarkSlugs]).size;
}

function updateCounts() {
    state.counts.offline = state.downloadedSlugs.size;
    syncReaderDerivedCounts();

    for (const collection of state.collections) {
        const members = state.collectionMembers[String(collection.id)] ?? new Set();
        collection.count = members.size;
    }
}

updateCounts();

export function useLibrary() {
    function collectionSectionId(collectionId) {
        return `${COLLECTION_SECTION_PREFIX}${collectionId}`;
    }

    function parseCollectionSection(section) {
        if (! section.startsWith(COLLECTION_SECTION_PREFIX)) {
            return null;
        }

        const id = Number(section.slice(COLLECTION_SECTION_PREFIX.length));

        return Number.isFinite(id) ? id : null;
    }

    function getActiveCollection() {
        const collectionId = parseCollectionSection(state.activeSection);

        if (collectionId == null) {
            return null;
        }

        return state.collections.find((collection) => collection.id === collectionId) ?? null;
    }

    function isCollectionActive(collectionId) {
        return state.activeSection === collectionSectionId(collectionId);
    }

    function setAllResourcesCount(count) {
        state.counts.allResources = Math.max(0, Number(count) || 0);
    }

    function refreshReaderCounts() {
        state.bookmarkSlugs = loadBookmarkSlugs();
        syncReaderDerivedCounts();
    }

    function reloadFromStorage() {
        state.bookmarkSlugs = loadBookmarkSlugs();
        state.collections = loadCollections();
        state.collectionMembers = loadCollectionMembers();
        updateCounts();
    }

    function markResourceBookmarked(slug) {
        if (! slug || state.bookmarkSlugs.has(slug)) {
            updateCounts();
            return;
        }

        state.bookmarkSlugs.add(slug);
        persistBookmarkSlugs(state.bookmarkSlugs);
        updateCounts();
    }

    function setActiveSection(id) {
        state.activeSection = id;
    }

    function selectCollection(collectionId) {
        state.activeSection = collectionSectionId(collectionId);
    }

    function createCollection(name) {
        const trimmed = name.trim();

        if (! trimmed) {
            return null;
        }

        const nextId = state.collections.reduce((max, collection) => Math.max(max, collection.id), 0) + 1;
        const collection = { id: nextId, name: trimmed, count: 0 };

        state.collections.push(collection);
        state.collectionMembers[collectionKey(nextId)] = new Set();
        persistCollections(state.collections);
        persistCollectionMembers(state.collectionMembers);
        updateCounts();

        return collection;
    }

    function markDownloaded(slug) {
        state.downloadedSlugs.add(slug);
        updateCounts();
    }

    function unmarkDownloaded(slug) {
        state.downloadedSlugs.delete(slug);
        updateCounts();
    }

    function toggleBookmark(slug) {
        if (state.bookmarkSlugs.has(slug)) {
            state.bookmarkSlugs.delete(slug);
        } else {
            state.bookmarkSlugs.add(slug);
        }

        persistBookmarkSlugs(state.bookmarkSlugs);
        updateCounts();

        return state.bookmarkSlugs.has(slug);
    }

    function isBookmarked(slug) {
        return state.bookmarkSlugs.has(slug);
    }

    function collectionKey(collectionId) {
        return String(collectionId);
    }

    function isInCollection(collectionId, slug) {
        return state.collectionMembers[collectionKey(collectionId)]?.has(slug) ?? false;
    }

    function toggleCollectionMembership(collectionId, slug) {
        const key = collectionKey(collectionId);

        if (! state.collectionMembers[key]) {
            state.collectionMembers[key] = new Set();
        }

        const members = state.collectionMembers[key];

        if (members.has(slug)) {
            members.delete(slug);
            persistCollectionMembers(state.collectionMembers);
            updateCounts();

            return false;
        }

        members.add(slug);
        persistCollectionMembers(state.collectionMembers);
        updateCounts();

        return true;
    }

    async function syncDownloads() {
        try {
            const { data } = await window.axios.get('/downloads');
            const slugs = (data.data ?? [])
                .map((download) => download.resource?.slug)
                .filter(Boolean);

            for (const slug of slugs) {
                if (state.downloadedSlugs.has(slug)) {
                    continue;
                }

                state.downloadedSlugs.add(slug);
            }

            updateCounts();
        } catch {
            updateCounts();
        }
    }

    async function hydrateDownloads() {
        const slugs = await getDownloadedSlugs();
        state.downloadedSlugs = new Set(slugs);
        updateCounts();

        const legacySlugs = await hydrateLegacyDownloadSlugs();

        for (const slug of legacySlugs) {
            state.downloadedSlugs.delete(slug);
        }

        updateCounts();
    }

    function filterBySection(resources) {
        const section = state.activeSection;

        if (section === 'all') {
            return resources;
        }

        if (section === 'offline') {
            return resources.filter((r) => state.downloadedSlugs.has(r.slug));
        }

        if (section === 'bookmarks') {
            const pageBookmarkSlugs = getPageBookmarkResourceSlugs();

            return resources.filter((resource) => (
                state.bookmarkSlugs.has(resource.slug) || pageBookmarkSlugs.has(resource.slug)
            ));
        }

        if (section === 'notes') {
            const noteSlugs = getUserNoteResourceSlugs();

            return resources.filter((resource) => noteSlugs.has(resource.slug));
        }

        if (section === 'recent') {
            const order = new Map(state.recentSlugs.map((slug, index) => [slug, index]));
            return resources
                .filter((r) => order.has(r.slug))
                .sort((a, b) => order.get(a.slug) - order.get(b.slug));
        }

        const collectionId = parseCollectionSection(section);

        if (collectionId != null) {
            const members = state.collectionMembers[collectionKey(collectionId)] ?? new Set();

            return resources.filter((resource) => members.has(resource.slug));
        }

        return resources;
    }

    return {
        state,
        setActiveSection,
        selectCollection,
        createCollection,
        getActiveCollection,
        isCollectionActive,
        filterBySection,
        openDrawer: () => { state.drawerOpen = true; },
        closeDrawer: () => { state.drawerOpen = false; },
        toggleDrawer: () => { state.drawerOpen = !state.drawerOpen; },
        isDownloaded: (slug) => state.downloadedSlugs.has(slug),
        markDownloaded,
        unmarkDownloaded,
        syncDownloads,
        hydrateDownloads,
        toggleBookmark,
        isBookmarked,
        isInCollection,
        toggleCollectionMembership,
        setAllResourcesCount,
        refreshReaderCounts,
        reloadFromStorage,
        markResourceBookmarked,
    };
}
