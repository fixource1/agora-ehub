import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { initTheme } from './composables/useTheme';
import router from './router';
import App from './App.vue';

initTheme();

const app = createApp(App);

app.use(createPinia());
app.use(router);
app.mount('#app');
