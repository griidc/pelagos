import 'devextreme/dist/css/dx.common.css';
import 'bootstrap';
import { createApp } from 'vue';
import StatsApp from './vue/Stats';

const statsApp = createApp({
  el: '#stats',
  components: { StatsApp },
  template: '<StatsApp/>',
});

statsApp.mount('#stats');
