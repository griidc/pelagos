import Vue from 'vue';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import VTooltip from 'v-tooltip';
import Loading from 'vue-loading-overlay';
import InformationProductSearchApp from '../vue/pages/InformationProductSearchApp';
import '@fortawesome/fontawesome-free/css/all.min.css';
import '../../css/search-ui.css';
import 'vue-loading-overlay/dist/vue-loading.css';
import filters from '../vue/utils/filters';

window.addEventListener('load', () => {
  Vue.use(BootstrapVue);
  Vue.use(IconsPlugin);
  Vue.use(Loading);
  Vue.use(VTooltip);
  Vue.filter('truncate', (text, length) => filters.truncate(text, length));
  // eslint-disable-next-line no-new
  new Vue({
    el: '#ip-search-app',
    components: { InformationProductSearchApp },
    template: `<div class="bootstrap">
                    <InformationProductSearchApp/>
                   </div>`,
  });
});
