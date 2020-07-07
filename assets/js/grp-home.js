import Vue from "vue";
import '../css/grp-home.css';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import FundingCyclesList from "./vue/components/FundingCyclesList";

Vue.use(BootstrapVue);

new Vue({
    components: { FundingCyclesList },
    data() {
        return {
            fundingCycles: window.fundingCycles,
            projectDirectors: window.projectDirectors
        };
    },
    template: `<FundingCyclesList :fundingCycles="fundingCycles" :projectDirectors="projectDirectors"/>`,
}).$mount('#picklist');

