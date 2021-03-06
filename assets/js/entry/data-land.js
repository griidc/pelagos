import Vue from 'vue';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import FileManager from '../vue/FileManager.vue';
import '../../css/file-manager.css';
import '@fortawesome/fontawesome-free/css/all.min.css';
import DownloadZipBtn from '../vue/components/data-land/DownloadZipBtn.vue';

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
} else if (geovizMap.attr('wkt') !== '') {
  dlmap.addFeatureFromWKT(geovizMap.attr('wkt'), { udi: geovizMap.attr('udi') });
  dlmap.gotoAllFeatures();
}
