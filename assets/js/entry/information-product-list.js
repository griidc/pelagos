import { createApp } from 'vue';
import { BootstrapVue } from 'bootstrap-vue';
import InformationProductListApp from '@/vue/InformationProductListApp';

const app = createApp({
  components: { InformationProductListApp },
  template: '<InformationProductListApp/>',
});

app.use(BootstrapVue);
app.mount('#information-product-list');
