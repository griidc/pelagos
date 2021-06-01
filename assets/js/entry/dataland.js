import Vue from "vue";
import Dataland from "@/vue/pages/Dataland";
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import {BootstrapVue, IconsPlugin} from "bootstrap-vue";
import '@fortawesome/fontawesome-free/css/all.min.css';

Vue.use(BootstrapVue);
Vue.use(IconsPlugin);
Vue.use(Loading);
new Vue({
    components: { Dataland },
    data() {
        return {
            udi: String
        }
    },
    beforeMount() {
        this.udi = this.$el.attributes['data-name'].value;
    },
    template: `<div class="bootstrap"><Dataland :udi="udi"/></div>`,
}).$mount("#dataland");
