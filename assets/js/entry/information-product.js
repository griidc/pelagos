import { createApp } from 'vue';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import InformationProductApp from '@/vue/InformationProductApp';
import '../../css/search-ui.css';
import '@fortawesome/fontawesome-free/css/all.min.css';

const app = createApp({
  components: { InformationProductApp },
  template: '<InformationProductApp/>',
});

app.use(BootstrapVue);
app.use(IconsPlugin);
app.mount('#information-product');
