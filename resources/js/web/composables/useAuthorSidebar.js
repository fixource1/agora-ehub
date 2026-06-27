import { ref } from 'vue';

const STORAGE_KEY = 'agora-author-sidebar-collapsed';

const collapsed = ref(loadCollapsed());

function loadCollapsed() {
    return localStorage.getItem(STORAGE_KEY) === '1';
}

export function useAuthorSidebar() {
    function toggle() {
        collapsed.value = !collapsed.value;
        localStorage.setItem(STORAGE_KEY, collapsed.value ? '1' : '0');
    }

    function expand() {
        collapsed.value = false;
        localStorage.setItem(STORAGE_KEY, '0');
    }

    return {
        collapsed,
        toggle,
        expand,
    };
}
