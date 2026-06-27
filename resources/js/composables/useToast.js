import { ref } from 'vue';

const message = ref(null);
let timer = null;

export function useToast() {
    function showToast(text, durationMs = 2400) {
        message.value = text;
        clearTimeout(timer);
        timer = window.setTimeout(() => {
            message.value = null;
        }, durationMs);
    }

    return {
        message,
        showToast,
    };
}
