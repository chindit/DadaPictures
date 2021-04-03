import { createApp } from 'vue';
import { i18n } from '@/locales';
import App from './App.vue';
import './registerServiceWorker';
import router from './router';
import store from './store';
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import '@fortawesome/fontawesome-free/css/all.min.css';
import '@fortawesome/fontawesome-free/js/all.min';

createApp(App).use(store).use(router).use(i18n)
  .mount('#app');
