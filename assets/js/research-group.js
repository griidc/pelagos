import Vue from 'vue';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import VTooltip from 'v-tooltip';
import ResearchGroupApp from './vue/ResearchGroupApp.vue';
import filters from './vue/utils/filters';

window.addEventListener('load', () => {
  Vue.use(BootstrapVue);
  Vue.use(IconsPlugin);
  Vue.use(Loading);
  Vue.use(VTooltip);
  Vue.filter('truncate', (text, length) => filters.truncate(text, length));
  Vue.filter('sort', (valuePath, array) => filters.sort(valuePath, array));

  // eslint-disable-next-line no-new
  new Vue({
    el: '#research-group',
    data() {
      return {
        researchGroupId: 0,
      };
    },
    beforeMount() {
      this.researchGroupId = Number(this.$el.attributes['data-name'].value);
    },
    components: { ResearchGroupApp },
    template: `<div class="bootstrap">
                        <ResearchGroupApp :id="researchGroupId" />
                   </div>`,
  });
});
