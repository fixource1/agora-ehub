import { reactive } from 'vue';

const state = reactive({
    drawerOpen: false,
    activeSection: 'offline',
    counts: {
        offline: 4,
        allResources: 10,
        notes: 3,
        bookmarks: 4,
    },
    collections: [
        { id: 1, name: 'DOST GIA & Grants', count: 3 },
        { id: 2, name: 'REPS Guidelines', count: 2 },
        { id: 3, name: 'OVCRE Training', count: 3 },
        { id: 4, name: 'Research Ethics', count: 1 },
    ],
    downloadedSlugs: new Set([
        'dost-gia-guidelines-and-procedures',
        'reps-guidelines-handbook',
        'dost-gia-monitoring-and-evaluation-manual',
        'uplb-research-ethics-review-protocol',
    ]),
    bookmarkSlugs: new Set([
        'dost-gia-guidelines-and-procedures',
        'reps-guidelines-handbook',
        'research-proposal-writing-for-ovcre',
        'reps-performance-evaluation-toolkit',
    ]),
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

export function useLibrary() {
    function setActiveSection(id) {
        state.activeSection = id;
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

        return resources;
    }

    return {
        state,
        setActiveSection,
        filterBySection,
        openDrawer: () => { state.drawerOpen = true; },
        closeDrawer: () => { state.drawerOpen = false; },
        toggleDrawer: () => { state.drawerOpen = !state.drawerOpen; },
        isDownloaded: (slug) => state.downloadedSlugs.has(slug),
        markDownloaded: (slug) => state.downloadedSlugs.add(slug),
    };
}
