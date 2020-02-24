import Vue from "vue";
import SearchApp from "./vue/SearchApp";
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
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
        template: `<SearchApp/>`,
        created() {
            console.log('hello')
        }
    });
});
