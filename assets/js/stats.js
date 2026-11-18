import 'devextreme/dist/css/dx.common.css';
import 'bootstrap';
import { createApp } from 'vue';
import StatsApp from './vue/Stats.vue';

const app = createApp({
  components: { StatsApp },
  template: '<StatsApp/>',
});

app.mount('#stats');
