import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { initTheme } from './composables/useTheme';
import App from './web/App.vue';
import router from './web/router';

initTheme();

const app = createApp(App);

app.use(createPinia());
app.use(router);
app.mount('#web-app');
