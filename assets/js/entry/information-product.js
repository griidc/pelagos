import Vue from 'vue';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import InformationProductApp from '@/vue/InformationProductApp';
import '../../css/search-ui.css';
import filters from '@/vue/utils/filters';

Vue.use(BootstrapVue);
Vue.use(IconsPlugin);
Vue.filter('truncate', (text, length) => filters.truncate(text, length));

// eslint-disable-next-line no-new
new Vue({
  el: '#information-product',
  components: { InformationProductApp },
  template: '<InformationProductApp/>',
});
