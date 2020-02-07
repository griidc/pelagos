import Vue from "vue";
import SearchApp from "./SearchApp";
import '../../scss/bootstrap.scss';
import '@fortawesome/fontawesome-free/css/all.min.css';
import '../../css/search-ui.css';

new Vue({
    components: { SearchApp },
    template: "<SearchApp/>",
    created() {
        console.log('hello')
    }
}).$mount("#app");

