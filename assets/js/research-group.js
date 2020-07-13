import Vue from "vue";
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import ResearchGroupApp from "./vue/ResearchGroupApp";
import VTooltip from 'v-tooltip';
import filters from "./vue/utils/filters";

window.addEventListener("load", function(event) {

    Vue.use(BootstrapVue);
    Vue.use(IconsPlugin);
    Vue.use(Loading);
    Vue.use(VTooltip);
    Vue.filter('truncate', (text, length) => {
        return filters.truncate(text, length);
    });
    Vue.filter('sort', (valuePath, array) => {
        return filters.sort(valuePath, array);
    })

    // here is the Vue code
    new Vue({
        el: '#research-group',
        data() {
         return {
             researchGroupId: 0
         }
        },
        beforeMount() {
            this.researchGroupId = Number(this.$el.attributes['data-name'].value);
        },
        components: { ResearchGroupApp },
        template: `<div class="bootstrap">
                        <ResearchGroupApp :id="researchGroupId" />
                   </div>`
    });
});
