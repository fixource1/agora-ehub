import { reactive } from 'vue';

const DOWNLOADS_STORAGE_KEY = 'agora-downloaded-slugs';
const BOOKMARKS_STORAGE_KEY = 'agora-bookmark-slugs';
const COLLECTIONS_STORAGE_KEY = 'agora-collection-members';
const COLLECTIONS_LIST_STORAGE_KEY = 'agora-collections';

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

function loadDownloadedSlugs() {
    try {
        const stored = localStorage.getItem(DOWNLOADS_STORAGE_KEY);

        if (stored) {
            const slugs = JSON.parse(stored);

            if (Array.isArray(slugs)) {
                return new Set(slugs);
            }
        }
    } catch {
        // Ignore malformed storage.
    }

    return new Set();
}

function loadBookmarkSlugs() {
    try {
        const stored = localStorage.getItem(BOOKMARKS_STORAGE_KEY);

        if (stored) {
            const slugs = JSON.parse(stored);

            if (Array.isArray(slugs)) {
                return new Set(slugs);
            }
        }
    } catch {
        // Ignore malformed storage.
    }

    return new Set(DEFAULT_BOOKMARKS);
}

function loadCollectionMembers() {
    try {
        const stored = localStorage.getItem(COLLECTIONS_STORAGE_KEY);

        if (stored) {
            const parsed = JSON.parse(stored);
            const members = {};

            for (const [collectionId, slugs] of Object.entries(parsed)) {
                if (Array.isArray(slugs)) {
                    members[collectionId] = new Set(slugs);
                }
            }

            return members;
        }
    } catch {
        // Ignore malformed storage.
    }

    return Object.fromEntries(
        Object.entries(DEFAULT_COLLECTION_MEMBERS).map(([id, slugs]) => [String(id), new Set(slugs)]),
    );
}

function persistDownloadedSlugs(slugs) {
    localStorage.setItem(DOWNLOADS_STORAGE_KEY, JSON.stringify([...slugs]));
}

function persistBookmarkSlugs(slugs) {
    localStorage.setItem(BOOKMARKS_STORAGE_KEY, JSON.stringify([...slugs]));
}

function loadCollections() {
    try {
        const stored = localStorage.getItem(COLLECTIONS_LIST_STORAGE_KEY);

        if (stored) {
            const parsed = JSON.parse(stored);

            if (Array.isArray(parsed) && parsed.length > 0) {
                return parsed.map((collection) => ({
                    id: collection.id,
                    name: collection.name,
                    count: 0,
                }));
            }
        }
    } catch {
        // Ignore malformed storage.
    }

    return DEFAULT_COLLECTIONS.map((collection) => ({ ...collection }));
}

function persistCollections(collections) {
    localStorage.setItem(
        COLLECTIONS_LIST_STORAGE_KEY,
        JSON.stringify(collections.map(({ id, name }) => ({ id, name }))),
    );
}

function persistCollectionMembers(members) {
    const payload = Object.fromEntries(
        Object.entries(members).map(([id, slugs]) => [id, [...slugs]]),
    );

    localStorage.setItem(COLLECTIONS_STORAGE_KEY, JSON.stringify(payload));
}

const state = reactive({
    drawerOpen: false,
    activeSection: 'offline',
    counts: {
        offline: 0,
        allResources: 10,
        notes: 3,
        bookmarks: 0,
    },
    collections: loadCollections(),
    downloadedSlugs: loadDownloadedSlugs(),
    bookmarkSlugs: loadBookmarkSlugs(),
    collectionMembers: loadCollectionMembers(),
    noteSlugs: new Set([
        'dost-gia-monitoring-and-evaluation-manual',
        'extension-project-implementation-guide',
        'uplb-research-ethics-review-protocol',
    ]),
    recentSlugs: [
        'introduction-to-uplb-ovcre-services',
        'technology-transfer-and-commercialization-primer',
        'dost-gia-guidelines-and-procedures',
        'research-proposal-writing-for-ovcre',
    ],
});

function updateCounts() {
    state.counts.offline = state.downloadedSlugs.size;
    state.counts.bookmarks = state.bookmarkSlugs.size;

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
        persistDownloadedSlugs(state.downloadedSlugs);
        updateCounts();
    }

    function unmarkDownloaded(slug) {
        state.downloadedSlugs.delete(slug);
        persistDownloadedSlugs(state.downloadedSlugs);
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
            const { data } = await window.axios.get('/api/v1/downloads');
            const slugs = (data.data ?? [])
                .map((download) => download.resource?.slug)
                .filter(Boolean);

            state.downloadedSlugs = new Set(slugs);
            persistDownloadedSlugs(state.downloadedSlugs);
            updateCounts();
        } catch {
            updateCounts();
        }
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
            return resources.filter((r) => state.bookmarkSlugs.has(r.slug));
        }

        if (section === 'notes') {
            return resources.filter((r) => state.noteSlugs.has(r.slug));
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
        toggleBookmark,
        isBookmarked,
        isInCollection,
        toggleCollectionMembership,
    };
}
