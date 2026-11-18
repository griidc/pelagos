import { createApp } from 'vue';
import { BootstrapVue } from 'bootstrap-vue';
import '../css/grp-home.css';
import FundingCyclesList from './vue/components/FundingCyclesList.vue';

const app = createApp({
  components: { FundingCyclesList },
  data() {
    return {
      fundingCycles: window.fundingCycles,
      projectDirectors: window.projectDirectors,
    };
  },
  template: '<FundingCyclesList :fundingCycles="fundingCycles" :projectDirectors="projectDirectors"/>',
});

app.use(BootstrapVue);
app.mount('#picklist');
