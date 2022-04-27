import Vue from 'vue';
import { BootstrapVue } from 'bootstrap-vue';
import InformationProductListApp from '@/vue/InformationProductListApp';

Vue.use(BootstrapVue);

// eslint-disable-next-line no-new
new Vue({
  el: '#information-product-list',
  components: { InformationProductListApp },
  template: '<InformationProductListApp/>',
});
