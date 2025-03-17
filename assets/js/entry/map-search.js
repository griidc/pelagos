import '../../scss/map-search.scss';
import '../modules/cardClick';

import * as Leaflet from 'leaflet';
import 'esri-leaflet';
import * as EsriLeafletVector from 'esri-leaflet-vector';
import '../../css/leaflet-custom.css';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min';

const leafletMap = document.getElementById('leaflet-map');

if (typeof (leafletMap) !== 'undefined' && leafletMap != null) {
  const esriApiKey = process.env.ESRI_API_KEY;

  const GRIIDCStyle = {
    color: 'orange',
    weight: 4,
    opacity: 0,
    fillOpacity: 0,
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

  const url = `${Routing.generate('pelagos_api_datasets_all_geojson')}`;
  // eslint-disable-next-line no-unused-vars
  let geojsonLayer = null;
  fetch(url).then((response) => response.json()).then((response) => {
    const geojsonMarkerOptions = {
      radius: 12,
      fill: false,
      weight: 4,
      opacity: 1,
    };
    geojsonLayer = Leaflet.geoJSON(response, {
      pointToLayer(feature, latlng) {
        return Leaflet.circleMarker(latlng, geojsonMarkerOptions);
      },
      style: GRIIDCStyle,
      onEachFeature(feature, layer) {
        layer.bindTooltip(feature.properties.name.toString(), { permanent: false, className: 'label' });
      },
    }).addTo(map);
    // const bounds = geojsonLayer.getBounds();
    // map.fitBounds(bounds, { padding: [20, 20] });
  });
}
