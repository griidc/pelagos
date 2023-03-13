import Vue, { createApp } from 'vue';
import { BootstrapVue } from 'bootstrap-vue';
import '../css/grp-home.css';
import FundingCyclesList from './vue/components/FundingCyclesList';

Vue.use(BootstrapVue);

createApp({
  components: { FundingCyclesList },
  data() {
    return {
      fundingCycles: window.fundingCycles,
      projectDirectors: window.projectDirectors,
    };
  },
  template: '<FundingCyclesList :fundingCycles="fundingCycles" :projectDirectors="projectDirectors"/>',
}).mount('#picklist');
