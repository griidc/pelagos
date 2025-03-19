import '../../scss/map-search.scss';
import '../modules/cardClick';
import $ from 'jquery';
import 'devextreme/integration/jquery';
import 'devextreme/ui/data_grid';
import 'devextreme/scss/bundles/dx.light.scss';

import * as Leaflet from 'leaflet';
import 'esri-leaflet';
import * as EsriLeafletVector from 'esri-leaflet-vector';
import '../../css/leaflet-custom.css';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min';

const esriApiKey = process.env.ESRI_API_KEY;

const GRIIDCStyle = {
  color: 'orange',
  weight: 4,
  opacity: 0,
  fillOpacity: 0,
};

const geojsonMarkerOptions = {
  radius: 12,
  fill: false,
  weight: 4,
  opacity: 1,
};

const map = Leaflet.map('leaflet-map', {
  preferCanvas: true,
  minZoom: 2,
  maxZoom: 14,
  attributionControl: true,
  worldCopyJump: true,
});

const basemapEnum = 'ArcGIS:Imagery';
EsriLeafletVector.vectorBasemapLayer(basemapEnum, {
  apiKey: esriApiKey,
}).addTo(map);

Leaflet.featureGroup().addTo(map);
map.setView([27.5, -97.5], 3);

let geojsonLayer = null;

const url = `${Routing.generate('pelagos_api_datasets_all_geojson')}`;
fetch(url).then((response) => response.json()).then((response) => {
  geojsonLayer = Leaflet.geoJSON(response, {
    pointToLayer(feature, latlng) {
      return Leaflet.circleMarker(latlng, geojsonMarkerOptions);
    },
    style: GRIIDCStyle,
  }).addTo(map);

  // const bounds = geojsonLayer.getBounds();
  // map.fitBounds(bounds, { padding: [20, 20] });
});

function showGeometryByUDI(id) {
  if (geojsonLayer === null) {
    return;
  }
  geojsonLayer.eachLayer((layer) => {
    if (layer.feature.properties.id === id) {
      const { feature } = layer;
      layer.setStyle({ opacity: 1 });
      layer.bindTooltip(feature.properties.name.toString(), { permanent: true, className: 'label' });
    }
  });
}

function hideGeometryByUDI(id) {
  if (geojsonLayer === null) {
    return;
  }
  geojsonLayer.eachLayer((layer) => {
    if (layer.feature.properties.id === id) {
      layer.setStyle({ opacity: 0 });
      layer.bindTooltip(null);
    }
  });
}

$(() => {
  $('#datasets-grid').dxDataGrid({
    dataSource: '/api/datasetsjson',
    columns: ['UDI', 'title'],
    showBorders: true,
    hoverStateEnabled: true,
    onCellHoverChanged(e) {
      if (e.eventType === 'mouseover') {
        if (e.data && e.data.UDI) {
          showGeometryByUDI(e.data.UDI);
        }
      } else if (e.eventType === 'mouseout') {
        if (e.data && e.data.UDI) {
          hideGeometryByUDI(e.data.UDI);
        }
      }
    },
  });
});
