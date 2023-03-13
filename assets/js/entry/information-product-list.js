import { createApp } from 'vue';
import InformationProductListApp from '@/vue/InformationProductListApp';

const infoProdListApp = createApp({
  components: { InformationProductListApp },
  template: '<InformationProductListApp/>',
});

infoProdListApp.mount('#information-product-list');
