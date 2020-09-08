import "devextreme/dist/css/dx.common.css";
import 'bootstrap';
import Vue from 'vue';
import StatsApp from '@/vue/Stats.vue';

new Vue({
  el: '#stats',
  components: { StatsApp },
  template: '<StatsApp/>'
});
