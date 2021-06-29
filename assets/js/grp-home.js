import Vue from 'vue';
import { BootstrapVue } from 'bootstrap-vue';
import '../css/grp-home.css';
import FundingCyclesList from './vue/components/FundingCyclesList.vue';
import filters from './vue/utils/filters';

Vue.use(BootstrapVue);
Vue.filter('truncate', (text, length) => filters.truncate(text, length));
new Vue({
  components: { FundingCyclesList },
  data() {
    return {
      fundingCycles: window.fundingCycles,
      projectDirectors: window.projectDirectors,
    };
  },
  template: '<FundingCyclesList :fundingCycles="fundingCycles" :projectDirectors="projectDirectors"/>',
}).$mount('#picklist');
