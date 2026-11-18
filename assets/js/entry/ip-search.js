import { createApp } from 'vue';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import VTooltip from 'v-tooltip';
import Loading from 'vue-loading-overlay';
import InformationProductSearchApp from '../vue/pages/InformationProductSearchApp.vue';
import '@fortawesome/fontawesome-free/css/all.min.css';
import '../../css/search-ui.css';
import 'vue-loading-overlay/dist/vue-loading.css';

window.addEventListener('load', () => {
  const app = createApp({
    components: { InformationProductSearchApp },
    template: `<div class="bootstrap">
                    <InformationProductSearchApp/>
                   </div>`,
  });
  app.use(BootstrapVue);
  app.use(IconsPlugin);
  app.use(Loading);
  app.use(VTooltip);
  app.mount('#ip-search-app');
});
