import Vue from "vue";
import SearchApp from "./vue/SearchApp";
import '../scss/bootstrap.scss';
import '@fortawesome/fontawesome-free/css/all.min.css';
import '../css/search-ui.css';

window.addEventListener("load", function(event) {
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
