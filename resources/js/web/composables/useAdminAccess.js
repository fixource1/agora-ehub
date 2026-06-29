import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';

export function useAdminAccess() {
    const auth = useAuthStore();

    const canManageUsers = computed(() => Boolean(auth.user?.can_manage_users));
    const canManageCategories = computed(() => Boolean(auth.user?.can_manage_categories));
    const canManageResources = computed(() => Boolean(auth.user?.can_manage_resources));
    const isAdministrator = computed(() => auth.user?.role === 'administrator');

    return {
        canManageUsers,
        canManageCategories,
        canManageResources,
        isAdministrator,
    };
}
