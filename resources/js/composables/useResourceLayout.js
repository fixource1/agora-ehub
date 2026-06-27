import { computed, ref } from 'vue';

const STORAGE_KEY = 'agora-resource-layout';

export const RESOURCE_LAYOUTS = [
    { id: 'books', label: 'Compact', title: '6-column grid' },
    { id: 'books-large', label: 'Comfortable', title: '4-column grid' },
    { id: 'list', label: 'List', title: 'List view' },
];

const layout = ref(loadLayout());

function loadLayout() {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (RESOURCE_LAYOUTS.some((item) => item.id === saved)) {
        return saved;
    }
    return 'books';
}

export function useResourceLayout() {
    const layoutClass = computed(() => {
        if (layout.value === 'books-large') return 'resource-layout-books-lg';
        if (layout.value === 'list') return 'resource-layout-list';
        return 'resource-layout-books';
    });

    function setLayout(id) {
        if (!RESOURCE_LAYOUTS.some((item) => item.id === id)) return;
        layout.value = id;
        localStorage.setItem(STORAGE_KEY, id);
    }

    return {
        layout,
        layoutClass,
        layouts: RESOURCE_LAYOUTS,
        setLayout,
    };
}
