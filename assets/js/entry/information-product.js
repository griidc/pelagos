import Vue, { createApp } from 'vue';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import InformationProductApp from '@/vue/InformationProductApp';
import '../../css/search-ui.css';
import '@fortawesome/fontawesome-free/css/all.min.css';

Vue.use(BootstrapVue);
Vue.use(IconsPlugin);

const infoProductApp = createApp({
  components: { InformationProductApp },
  template: '<InformationProductApp/>',
});

infoProductApp.mount('#information-product');
