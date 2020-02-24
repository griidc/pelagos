import Vue from "vue";
import SearchApp from "./vue/SearchApp";
import '../scss/bootstrap.scss';
import '@fortawesome/fontawesome-free/css/all.min.css';
import '../css/search-ui.css';
// import axios from "axios";
// import VueAxios from 'vue-axios';
window.addEventListener("load", function(event) {
    // Vue.use(VueAxios, axios);

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
