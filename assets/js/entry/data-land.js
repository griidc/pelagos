import Vue from 'vue';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import FileManager from '../vue/FileManager.vue';
import '../../css/file-manager.css';
import '@fortawesome/fontawesome-free/css/all.min.css';
import DownloadZipBtn from '../vue/components/data-land/DownloadZipBtn.vue';
import HelpModal from '../vue/components/data-land/HelpModal.vue';
import '../../scss/data-land.scss';

// Mount File Manager vue component
const fileManagerElement = document.getElementById('file-manager-app');
if (fileManagerElement) {
  const datasetSubmissionId = Number(fileManagerElement.dataset.submission);
  // eslint-disable-next-line no-new
  new Vue({
    el: '#file-manager-app',
    data() {
      return {
        datasetSubmissionId,
      };
    },
    components: { FileManager },
    template: '<FileManager :datasetSubId="datasetSubmissionId" :writeMode="false"/>',
  });
}

// Mount Download button vue component
const downloadZipElement = document.getElementById('download-zip');
if (downloadZipElement) {
  const datasetId = downloadZipElement.dataset.id;
  Vue.use(BootstrapVue);
  Vue.use(IconsPlugin);
  // eslint-disable-next-line no-new
  new Vue({
    el: '#download-zip',
    components: { DownloadZipBtn },
    data() {
      return {
        datasetId,
      };
    },
    template: '<DownloadZipBtn :id="datasetId"/>',
  });
}

// Mount Help Modal vue component
const helpBtnElement = document.getElementById('help-btn');
if (helpBtnElement) {
  Vue.use(BootstrapVue);
  // eslint-disable-next-line no-new
  new Vue({
    el: '#help-btn',
    components: { HelpModal },
    template: '<HelpModal/>',
  });
}

// eslint-disable-next-line no-undef
const dlmap = new GeoViz();

dlmap.initMap('dlolmap', {
  onlyOneFeature: false, allowModify: false, allowDelete: false, staticMap: false, labelAttr: 'udi',
});

// eslint-disable-next-line no-undef
const geovizMap = $('#dlolmap');

if (geovizMap.attr('description') !== '' && geovizMap.attr('wkt') === '') {
  const imagePath = geovizMap.attr('labimage');
  dlmap.addImage(imagePath, 0.4);
  dlmap.makeStatic();
} else if (geovizMap.attr('wkt')) {
  dlmap.addFeatureFromWKT(geovizMap.attr('wkt'), { udi: geovizMap.attr('udi') });
  dlmap.gotoAllFeatures();
}

const metadataDownloadBtn = document.getElementById('metadata-download');
if (metadataDownloadBtn) {
  metadataDownloadBtn.addEventListener('click', () => {
    // eslint-disable-next-line no-undef
    window.location = Routing.generate(
      'pelagos_app_ui_dataland_metadata',
      { udi: metadataDownloadBtn.dataset.udi },
    );
  });
}

const metadataDownloadLink = document.getElementById('metadata-link');
if (metadataDownloadLink) {
  metadataDownloadLink.addEventListener('click', (event) => {
    event.preventDefault();
    window.open(
      // eslint-disable-next-line no-undef
      Routing.generate('pelagos_app_ui_dataland_formatted_metadata',
        { udi: metadataDownloadLink.dataset.udi }),
      '_blank',
    );
  });
}

const isTextClamped = (element) => element.scrollHeight > element.clientHeight;

const clampedText = document.getElementsByClassName('clamped');
clampedText.forEach((element) => {
  const link = document.createElement('a');
  link.text = 'More';
  link.title = 'Click for more...';
  link.href = '#';
  link.addEventListener('click', (event) => {
    const { target } = event;
    const clampedElement = target.previousElementSibling;
    if (clampedElement.classList.contains('clamped')) {
      target.text = 'Less';
      link.title = 'Click for less...';
      clampedElement.classList.remove('clamped');
    } else {
      target.text = 'More';
      link.title = 'Click for more...';
      clampedElement.classList.add('clamped');
    }
  });
  if (isTextClamped(element)) {
    element.parentNode.appendChild(link);
  }
});
