import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { initTheme } from './composables/useTheme';
import { repairOfflineStorage } from './composables/useOfflineStore';
import router from './router';
import App from './App.vue';

initTheme();
repairOfflineStorage();

const app = createApp(App);

app.use(createPinia());
app.use(router);
app.mount('#app');
