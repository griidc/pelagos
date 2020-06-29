import Vue from "vue";
import SearchApp from "./vue/SearchApp";
import '@fortawesome/fontawesome-free/css/all.min.css';
import '../css/search-ui.css';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import VTooltip from 'v-tooltip';

window.addEventListener("load", function(event) {

    Vue.use(BootstrapVue);
    Vue.use(IconsPlugin);
    Vue.use(Loading);
    Vue.use(VTooltip);
    // here is the Vue code
    new Vue({
        el: '#search-app',
        components: { SearchApp },
        data() {
            return {
               customBaseTemplate: ''
            }
        },
        beforeMount() {
            this.customBaseTemplate = this.$el.attributes['data-name'].value;
        },
        template: `<div class="bootstrap">
                    <SearchApp :template="this.customBaseTemplate"/>
                   </div>`
    });
});
