import * as Leaflet from 'leaflet';
import 'esri-leaflet';
import * as EsriLeafletVector from 'esri-leaflet-vector';
import '../../css/custom-pm-icons.css';
import '@geoman-io/leaflet-geoman-free';
import '@geoman-io/leaflet-geoman-free/dist/leaflet-geoman.css';
import { EventEmitter } from 'events';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min';
// import 'leaflet/dist/leaflet.css';

const geoVizEventEmitter = new EventEmitter();
const esriApiKey = process.env.ESRI_API_KEY;
const worldViewCode = process.env.WORLD_VIEW_CODE;

const styles = {
  defaultStyle:
  {
    color: '#fcaf08',
    // weight: 4,
    opacity: 1,
    fillOpacity: 0,
  },
  selectedStyle:
  {
    color: '#08fcaf',
    // weight: 4,
    opacity: 1,
    fillOpacity: 0,
  },
  hoverStyle:
  {
    color: '#af08fc',
    // weight: 4,
    opacity: 1,
    fillOpacity: 0,
  },
  markerStyle:
  {
    radius: 6,
    fill: false,
    // weight: 4,
    opacity: 1,
  },
};

function getV2BasemapLayer(style) {
  return EsriLeafletVector.vectorBasemapLayer(style, {
    token: esriApiKey,
    version: 2,
    worldview: worldViewCode,
  });
}

const ArcGISImagery = getV2BasemapLayer('arcgis/imagery');
const ArcGISOceans = getV2BasemapLayer('arcgis/oceans');
const ArcGISTerrain = getV2BasemapLayer('arcgis/terrain');

const mapStyles = {
  'ArcGIS Imagery': ArcGISImagery,
  'ArcGIS Oceans': ArcGISOceans,
  'ArcGIS Terrain': ArcGISTerrain,
};

const map = Leaflet.map('leaflet-map', {
  preferCanvas: true,
  minZoom: 2,
  maxZoom: 14,
  attributionControl: true,
  worldCopyJump: true,
  layers: [ArcGISImagery],
});

const controlLayer = Leaflet.control.layers(mapStyles).addTo(map);

const goHome = () => {
  map.setZoom(3, { animate: true });
  map.panTo([27.5, -97.5], { animate: true, duration: 1 });
};

// Opt out for all layers for PM controls
Leaflet.PM.setOptIn(true);

// add Leaflet-Geoman controls with some options to the map
map.pm.addControls({
  position: 'topleft',
  drawCircleMarker: false,
  drawMarker: false,
  drawPolyline: false,
  drawRectangle: true,
  drawPolygon: true,
  drawCircle: false,
  drawText: false,
  cutPolygon: false,
  editMode: true,
  dragMode: false,
  removalMode: true,
  rotateMode: false,
});

map.pm.Toolbar.createCustomControl(
  {
    name: 'Home',
    block: 'custom',
    title: 'Navigate to Home',
    className: 'custom-pm-icon-home',
    onClick: () => {
      goHome();
    },
  },
);

map.pm.Toolbar.changeControlOrder([
  'Home',
]);

// map.pm.setPathOptions({
//   color: 'blue',
//   fillOpacity: 0,
// });

let drawnLayer;
// Function to handle the map filter drawn event
map.on('pm:create', (e) => {
  drawnLayer = e.layer;
  // Allow PM to manage the layer
  drawnLayer.options.pmIgnore = false;
  Leaflet.PM.reInitLayer(drawnLayer);
  const geojson = drawnLayer.toGeoJSON();
  if (geojson) {
    geoVizEventEmitter.emit('geojsonupdated', { geojson });
  }
  drawnLayer.on('pm:disable', (event) => {
    const editedGeojson = event.target.toGeoJSON();
    if (editedGeojson) {
      geoVizEventEmitter.emit('geojsonupdated', { geojson: editedGeojson });
    }
  });
});

['pm:globaleditmodetoggled', 'pm:globalremovalmodetoggled'].forEach((eventName) => {
  map.on(eventName, () => {
    if (drawnLayer) {
      drawnLayer.bringToFront();
    }
  });
});
map.on('pm:remove', () => {
  geoVizEventEmitter.emit('geojsonupdated', { geojson: null });
});

// Listen for the drawstart event and clear the previously drawn features, if any.
map.on('pm:drawstart', () => {
  if (drawnLayer) {
    drawnLayer.off();
    map.removeLayer(drawnLayer);
  }
});

const features = Leaflet.featureGroup().addTo(map);
const selectedFeatures = Leaflet.featureGroup().addTo(map);
map.setView([27.5, -97.5], 3);
let geojsonLayer = null;

const url = `${Routing.generate('pelagos_map_all_geojson')}`;
fetch(url).then((response) => response.json()).then((response) => {
  geojsonLayer = Leaflet.geoJSON(response, {
    pointToLayer(feature, latlng) {
      return Leaflet.circleMarker(latlng, styles.markerStyle);
    },
    onEachFeature(feature, layer) {
      layer.bindTooltip(feature.properties.name.toString(), { permanent: false, className: 'label' });
      layer.on('mouseover', (e) => e.target.setStyle(styles.hoverStyle));
      layer.on('mouseout', (e) => e.target.setStyle(styles.defaultStyle));
    },
    style: styles.defaultStyle,
    markersInheritOptions: true,
  });
  controlLayer.addOverlay(geojsonLayer, 'Show All Features');
});

const addToSelectedLayer = (list) => {
  selectedFeatures.clearLayers();
  controlLayer.removeLayer(selectedFeatures);

  list.forEach((geojson) => {
    Leaflet.geoJSON(geojson, {
      pointToLayer: (feature, latlng) => Leaflet.circleMarker(latlng, styles.markerStyle),
      style: { color: '#08fcaf', opacity: 1, fillOpacity: 0 },
      markersInheritOptions: true,
      onEachFeature: (feature, layer) => {
        layer.bindTooltip(feature.properties.name.toString(), { permanent: false, className: 'label' });
        layer.on('click', (e) => geoVizEventEmitter.emit('featureselected', { feature: e.target.feature }));
        layer.on('mouseover', (e) => e.target.setStyle(styles.hoverStyle));
        layer.on('mouseout', (e) => e.target.setStyle(styles.selectedStyle));
      },
    }).addTo(selectedFeatures);
  });

  if (selectedFeatures.getLayers().length) {
    controlLayer.addOverlay(selectedFeatures, 'Selected Features');
    map.fitBounds(selectedFeatures.getBounds(), { padding: [20, 20] });
  } else {
    goHome();
  }
};

const resetFeatures = () => {
  features.clearLayers();
  selectedFeatures.clearLayers();
  map.removeLayer(drawnLayer);
};

const showGeometryByUDI = (id) => {
  if (!geojsonLayer) return;
  geojsonLayer.eachLayer((layer) => {
    if (layer.feature.properties.id === id) {
      layer.bindTooltip(layer.feature.properties.name.toString(), { permanent: true, className: 'label' });
      layer.setStyle(styles.hoverStyle);
      if (!features.hasLayer(layer)) features.addLayer(layer);
    }
  });
};

const hideGeometryByUDI = (id) => {
  if (!geojsonLayer) return;
  geojsonLayer.eachLayer((layer) => {
    const { feature } = layer;
    if (feature.properties.id === id) {
      layer.bindTooltip(feature.properties.name.toString(), { permanent: false, className: 'label' });
      features.removeLayer(layer);
    }
  });
};

const zoomAndPanToFeature = (id) => {
  if (!geojsonLayer) return;
  geojsonLayer.eachLayer((layer) => {
    if (layer.feature.properties.id === id) {
      features.addLayer(layer);
      layer.bringToFront();
      map.fitBounds(layer.getBounds(), { padding: [20, 20] });
    }
  });
};

const on = (eventName, callback) => geoVizEventEmitter.on(eventName, callback);
const off = (eventName, callback) => geoVizEventEmitter.off(eventName, callback);
const once = (eventName, callback) => geoVizEventEmitter.once(eventName, callback);

export {
  addToSelectedLayer,
  resetFeatures,
  showGeometryByUDI,
  hideGeometryByUDI,
  zoomAndPanToFeature,
  on,
  off,
  once,
};
