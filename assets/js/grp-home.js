import Vue from "vue";
import '../css/grp-home.css';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import FundingCyclesList from "./vue/components/FundingCyclesList";
import filters from "./vue/utils/filters";

Vue.use(BootstrapVue);
Vue.filter('truncate', (text, length) => {
    return filters.truncate(text, length);
});
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

