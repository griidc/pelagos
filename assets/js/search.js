import Vue, { createApp } from 'vue';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import VTooltip from 'v-tooltip';
import Loading from 'vue-loading-overlay';
import SearchApp from './vue/SearchApp';
import '@fortawesome/fontawesome-free/css/all.min.css';
import '../css/search-ui.css';
import 'vue-loading-overlay/dist/vue-loading.css';

window.addEventListener('load', () => {
  Vue.use(BootstrapVue);
  Vue.use(IconsPlugin);
  Vue.use(Loading);
  Vue.use(VTooltip);
  const searchApp = createApp({
    components: { SearchApp },
    template: `<div class="bootstrap">
                    <SearchApp/>
                   </div>`,
  });

  searchApp.mount('#search-app');
});
