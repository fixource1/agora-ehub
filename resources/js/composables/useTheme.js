import { computed, ref, watch } from 'vue';

const theme = ref('light');

function applyTheme(value) {
    theme.value = value;
    document.documentElement.classList.toggle('dark', value === 'dark');
    localStorage.setItem('agora-theme', value);
    const meta = document.querySelector('meta[name="theme-color"]');
    if (meta) {
        meta.setAttribute('content', value === 'dark' ? '#132E54' : '#00563F');
    }
}

export function initTheme() {
    const saved = localStorage.getItem('agora-theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    applyTheme(saved ?? (prefersDark ? 'dark' : 'light'));
}

export function useTheme() {
    const isDark = computed(() => theme.value === 'dark');

    function toggle() {
        applyTheme(theme.value === 'dark' ? 'light' : 'dark');
    }

    function setTheme(value) {
        applyTheme(value);
    }

    watch(theme, (value) => {
        document.documentElement.classList.toggle('dark', value === 'dark');
    });

    return { theme, isDark, toggle, setTheme };
}
