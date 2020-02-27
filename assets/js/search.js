import Vue from "vue";
import SearchApp from "./vue/SearchApp";
import '../scss/bootstrap.scss';
import '@fortawesome/fontawesome-free/css/all.min.css';
import '../css/search-ui.css';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';

window.addEventListener("load", function(event) {

    Vue.use(BootstrapVue);
    Vue.use(IconsPlugin);

    // here is the Vue code
    new Vue({
        el: '#search-app',
        components: { SearchApp },
        template: `<div class="bootstrap">
                    <SearchApp/>
                   </div>`
    });
});
