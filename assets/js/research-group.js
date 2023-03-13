import { createApp } from 'vue';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import VTooltip from 'v-tooltip';
import ResearchGroupApp from './vue/ResearchGroupApp';
import '../css/search-ui.css';
import '@fortawesome/fontawesome-free/css/all.min.css';

window.addEventListener('load', () => {
  const researchGroupApp = createApp({
    data() {
      return {
        researchGroupId: 0,
      };
    },
    beforeMount() {
      this.researchGroupId = Number(document.getElementById('research-group').dataset.name);
    },
    components: { ResearchGroupApp },
    template: `<div class="bootstrap">
                        <ResearchGroupApp :id="researchGroupId" />
                   </div>`,
  });

  researchGroupApp.mount('#research-group');
  researchGroupApp.use(BootstrapVue);
  researchGroupApp.use(IconsPlugin);
  researchGroupApp.use(Loading);
  researchGroupApp.use(VTooltip);
});
