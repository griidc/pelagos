import { createApp } from 'vue';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import VTooltip from 'v-tooltip';
import Loading from 'vue-loading-overlay';
import MultiSearchApp from '../vue/pages/MultiSearchApp.vue';
import '@fortawesome/fontawesome-free/css/all.min.css';
import '../../css/search-ui.css';
import 'vue-loading-overlay/dist/vue-loading.css';

window.addEventListener('load', () => {
  const app = createApp({
    components: { MultiSearchApp },
    template: `<div class="bootstrap">
                    <MultiSearchApp/>
                   </div>`,
  });
  app.use(BootstrapVue);
  app.use(IconsPlugin);
  app.use(Loading);
  app.use(VTooltip);
  app.mount('#multi-search-app');
});
