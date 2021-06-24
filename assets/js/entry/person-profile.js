import Vue from 'vue';
import PersonProfile from '@/vue/pages/PersonProfile';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import '@fortawesome/fontawesome-free/css/all.min.css';

Vue.use(BootstrapVue);
Vue.use(IconsPlugin);
Vue.use(Loading);
new Vue({
  components: { PersonProfile },
  data() {
    return {
      person: 0,
    };
  },
  beforeMount() {
    this.person = Number(this.$el.attributes['data-name'].value);
  },
  template: '<div class="bootstrap"><PersonProfile :personId=\'person\'/></div>',
}).$mount('#person-profile');
