import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { client, TOKEN_KEY } from '../api/client';

export const useAuthStore = defineStore('web-auth', () => {
    const user = ref(null);
    const token = ref(localStorage.getItem(TOKEN_KEY));
    const loading = ref(false);
    const initialized = ref(false);

    const isAuthenticated = computed(() => Boolean(token.value && user.value));

    async function initialize() {
        if (!token.value) {
            initialized.value = true;
            return;
        }

        try {
            const { data } = await client.get('/auth/user');
            user.value = data.data ?? data;
        } catch {
            token.value = null;
            user.value = null;
            localStorage.removeItem(TOKEN_KEY);
        } finally {
            initialized.value = true;
        }
    }

    async function login(email, password) {
        loading.value = true;
        try {
            const { data } = await client.post('/auth/login', { email, password });
            token.value = data.token;
            user.value = data.user;
            localStorage.setItem(TOKEN_KEY, data.token);
            return true;
        } finally {
            loading.value = false;
        }
    }

    async function logout() {
        try {
            await client.post('/auth/logout');
        } catch {
            // ignore
        }
        token.value = null;
        user.value = null;
        localStorage.removeItem(TOKEN_KEY);
    }

    return {
        user,
        token,
        loading,
        initialized,
        isAuthenticated,
        initialize,
        login,
        logout,
    };
});
