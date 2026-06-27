import { ref, shallowRef } from 'vue';

const isOpen = ref(false);
const resource = shallowRef(null);

export function useResourceQuickActions() {
    function openQuickActions(nextResource) {
        resource.value = nextResource;
        isOpen.value = true;
        document.body.classList.add('action-menu-open');
    }

    function closeQuickActions() {
        isOpen.value = false;
        document.body.classList.remove('action-menu-open');
        resource.value = null;
    }

    return {
        isOpen,
        resource,
        openQuickActions,
        closeQuickActions,
    };
}
