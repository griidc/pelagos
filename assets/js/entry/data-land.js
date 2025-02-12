import Vue from 'vue';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';

import * as Leaflet from 'leaflet';
import 'esri-leaflet';
import * as EsriLeafletVector from 'esri-leaflet-vector';
import '../../css/leaflet-custom.css';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min';
import FileManager from '../vue/FileManager.vue';
import '../../css/file-manager.css';
import '@fortawesome/fontawesome-free/css/all.min.css';
import DownloadZipBtn from '../vue/components/data-land/DownloadZipBtn.vue';
import HelpModal from '../vue/components/data-land/HelpModal.vue';
import '../../scss/data-land.scss';

const leafletMap = document.getElementById('leaflet-map');

if (typeof (leafletMap) !== 'undefined' && leafletMap != null) {
  const { datasetId } = leafletMap.dataset;
  const esriApiKey = process.env.ESRI_API_KEY;

  const GRIIDCStyle = {
    color: 'orange',
    weight: 4,
    opacity: 1,
    fillOpacity: 0.15,
  };

  const map = Leaflet.map('leaflet-map', {
    preferCanvas: true,
    minZoom: 3,
    maxZoom: 14,
    attributionControl: true,
    worldCopyJump: true,
  });

  const basemapEnum = 'ArcGIS:Imagery';
  EsriLeafletVector.vectorBasemapLayer(basemapEnum, {
    apiKey: esriApiKey,
  }).addTo(map);

  Leaflet.featureGroup().addTo(map);

  const url = `${Routing.generate('pelagos_app_ui_dataland_get_json')}/${datasetId}`;
  fetch(url).then((response) => response.json()).then((response) => {
    const geojsonMarkerOptions = {
      radius: 12,
      fill: false,
      weight: 4,
      opacity: 1,
    };
    const geojsonLayer = Leaflet.geoJson(response, {
      pointToLayer(feature, latlng) {
        return Leaflet.circleMarker(latlng, geojsonMarkerOptions);
      },
      style: GRIIDCStyle,
      onEachFeature(feature, layer) {
        layer.bindTooltip(feature.properties.name.toString(), { permanent: false, className: 'label' });
      },
    }).addTo(map);
    const bounds = geojsonLayer.getBounds();
    map.fitBounds(bounds, { padding: [20, 20] });
  });
}

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
    data() {
      return {
        alternativeTitle: 'Alternative Data Access Methods',
        width: 500,
        height: 500,
      };
    },
    template: `
    <HelpModal :helpBtnTitle="alternativeTitle" :width="width" :height="height">
      <div>
        <p>
          Some GRIIDC datasets have been made available via alternative means.
          These methods are provided as a convenience to the user. These
          methods may differ in format or organization from the originally
          submitted dataset files.
        </p>
        <ul>
          <li>
            <strong>NCEI</strong> - This dataset has been archived with NCEI.
            Note that this dataset may be part of a larger NCEI data package,
            e.g. one NCEI data package may consist of multiple GRIIDC
            datasets.
          </li>
          &nbsp;
          <li>
            <strong>ERDDAP</strong> - This dataset is also available via the
            GRIIDC ERDDAP instance. ERDDAP is a data server that provides a
            simple, consistent way to download subsets of scientific datasets
            in common file formats and make graphs and maps.
          </li>
        </ul>
    </div>
  </HelpModal>`,
  });
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
      Routing.generate(
        'pelagos_app_ui_dataland_formatted_metadata',
        { udi: metadataDownloadLink.dataset.udi },
      ),
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
  // eslint-disable-next-line no-script-url
  link.href = 'javascript:void(0)';
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
