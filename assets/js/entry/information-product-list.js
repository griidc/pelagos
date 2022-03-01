import Vue from 'vue';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import InformationProductListApp from '@/vue/InformationProductListApp';
import '../../css/search-ui.css';
import '@fortawesome/fontawesome-free/css/all.min.css';

Vue.use(BootstrapVue);
Vue.use(IconsPlugin);

// eslint-disable-next-line no-new
new Vue({
  el: '#information-product-list',
  components: { InformationProductListApp },
  template: '<InformationProductListApp/>',
});
