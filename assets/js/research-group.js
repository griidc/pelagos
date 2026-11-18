import { createApp } from 'vue';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import VTooltip from 'v-tooltip';
import ResearchGroupApp from './vue/ResearchGroupApp.vue';
import '../css/search-ui.css';
import '@fortawesome/fontawesome-free/css/all.min.css';

window.addEventListener('load', () => {
  const researchGroupElement = document.getElementById('research-group');
  if (researchGroupElement) {
    const researchGroupId = Number(researchGroupElement.dataset.name);
    const app = createApp({
      data() {
        return {
          researchGroupId,
        };
      },
      components: { ResearchGroupApp },
      template: `<div class="bootstrap">
                          <ResearchGroupApp :id="researchGroupId" />
                     </div>`,
    });
    app.use(BootstrapVue);
    app.use(IconsPlugin);
    app.use(Loading);
    app.use(VTooltip);
    app.mount('#research-group');
  }
});
