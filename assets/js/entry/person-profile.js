import Vue, { createApp } from 'vue';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import Loading from 'vue-loading-overlay';
import PersonProfile from '../vue/pages/PersonProfile';
import 'vue-loading-overlay/dist/vue-loading.css';
import '@fortawesome/fontawesome-free/css/all.min.css';

Vue.use(BootstrapVue);
Vue.use(IconsPlugin);
Vue.use(Loading);

createApp({
  components: { PersonProfile },
  data() {
    return {
      person: 0,
    };
  },
  beforeMount() {
    this.person = Number(document.getElementById('person-profile').dataset.name);
  },
  template: '<div class="bootstrap"><PersonProfile :personId=\'person\'/></div>',
}).mount('#person-profile');
