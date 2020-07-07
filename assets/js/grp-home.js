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
        };
    },
    template: `<FundingCyclesList :fundingCycles="fundingCycles" />`,
}).$mount('#picklist');

