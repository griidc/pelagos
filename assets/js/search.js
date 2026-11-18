import Vue, { createApp } from 'vue';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import VTooltip from 'v-tooltip';
import Loading from 'vue-loading-overlay';
import SearchApp from './vue/SearchApp.vue';
import '@fortawesome/fontawesome-free/css/all.min.css';
import '../css/search-ui.css';
import 'vue-loading-overlay/dist/vue-loading.css';

Vue.use(Loading);

window.addEventListener('load', () => {
  const app = createApp({
    components: { SearchApp },
    template: `<div class="bootstrap">
                    <SearchApp/>
                   </div>`,
  });
  app.use(BootstrapVue);
  app.use(IconsPlugin);
  app.use(VTooltip);
  app.mount('#search-app');
});
