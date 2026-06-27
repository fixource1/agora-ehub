<template>
    <div class="web-login bg-app flex min-h-screen items-center justify-center px-4">
        <div class="absolute right-4 top-4">
            <WebThemeToggle />
        </div>

        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <img :src="BRAND_LOGO_SRC" :alt="BRAND_LOGO_ALT" class="mx-auto mb-4 h-16 w-16 rounded-full object-cover shadow-sm">
                <h1 class="text-app text-2xl font-bold">{{ APP_NAME }} Author Portal</h1>
                <p class="text-muted mt-2 text-sm">Sign in to manage and publish institutional resources</p>
            </div>

            <form
                class="web-card p-8"
                @submit.prevent="submit"
            >
                <div v-if="error" class="web-error mb-4">{{ error }}</div>

                <label class="mb-4 block">
                    <span class="text-app mb-1.5 block text-sm font-medium">Email</span>
                    <input
                        v-model="email"
                        type="email"
                        required
                        autocomplete="email"
                        class="web-input"
                        placeholder="author@agora-ehub.local"
                    >
                </label>

                <label class="mb-6 block">
                    <span class="text-app mb-1.5 block text-sm font-medium">Password</span>
                    <input
                        v-model="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="web-input"
                        placeholder="••••••••"
                    >
                </label>

                <button
                    type="submit"
                    class="bg-brand hover:bg-brand/90 w-full rounded-xl px-4 py-3 text-sm font-semibold text-white transition disabled:opacity-60"
                    :disabled="auth.loading"
                >
                    {{ auth.loading ? 'Signing in...' : 'Sign in' }}
                </button>

                <p class="text-muted mt-6 text-center text-xs">
                    Administrators can also use
                    <a href="/admin" class="text-brand font-medium hover:underline">Filament Admin</a>
                </p>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { APP_NAME, BRAND_LOGO_ALT, BRAND_LOGO_SRC } from '@/constants/brand';
import WebThemeToggle from '../components/WebThemeToggle.vue';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const email = ref('');
const password = ref('');
const error = ref('');

async function submit() {
    error.value = '';
    try {
        await auth.login(email.value, password.value);
        const redirect = route.query.redirect ?? '/author';
        router.push(typeof redirect === 'string' ? redirect : '/author');
    } catch (e) {
        error.value = e.response?.data?.message
            ?? e.response?.data?.errors?.email?.[0]
            ?? 'Unable to sign in. Please check your credentials.';
    }
}
</script>
