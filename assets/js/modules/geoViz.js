/* eslint-disable class-methods-use-this */
import * as Leaflet from 'leaflet';
import 'esri-leaflet';
import * as EsriLeafletVector from 'esri-leaflet-vector';
import '../../css/custom-pm-icons.css';
import '@geoman-io/leaflet-geoman-free';
import '@geoman-io/leaflet-geoman-free/dist/leaflet-geoman.css';
import { EventEmitter } from 'events';
// import 'leaflet/dist/leaflet.css'; # This is broken due to webpack, but it is imported in the index.html.twig file.

// import { Modal } from 'flowbite';

// function showModal() {
//   const modal = document.getElementById('default-modal');
//   const modalInstance = new Modal(modal);
//   modalInstance.toggle();
// }

const geoVizEventEmitter = new EventEmitter();
const esriApiKey = process.env.ESRI_API_KEY;
const worldViewCode = process.env.WORLD_VIEW_CODE;
const INITIAL_ZOOM = 3;
const INITIAL_CENTER = [27.5, -97.5];

const getV2BasemapLayer = (style) => EsriLeafletVector.vectorBasemapLayer(style, {
  token: esriApiKey,
  version: 2,
  worldview: worldViewCode,
});

const ArcGISImagery = getV2BasemapLayer('arcgis/imagery');
const ArcGISOceans = getV2BasemapLayer('arcgis/oceans');
const ArcGISTerrain = getV2BasemapLayer('arcgis/terrain');

const mapStyles = {
  'ArcGIS Imagery': ArcGISImagery,
  'ArcGIS Oceans': ArcGISOceans,
  'ArcGIS Terrain': ArcGISTerrain,
};

let map = null;
let drawnGroup = null;
export default class GeoViz {
  constructor(element, options = {}) {
    map = Leaflet.map(element, {
      preferCanvas: true,
      minZoom: 2,
      maxZoom: 14,
      attributionControl: true,
      worldCopyJump: true,
      layers: [ArcGISImagery],
    });

    Leaflet.PM.setOptIn(true);

    map.pm.addControls({
      positions: {
        draw: 'topleft',
        edit: 'topleft',
        custom: 'topleft',
        options: 'bottomright',
      },
      drawMarker: false,
      drawCircleMarker: options.allowDrawPoint !== undefined ? options.allowDrawPoint : false,
      drawPolyline: options.allowDrawPolyline !== undefined ? options.allowDrawPolyline : false,
      drawRectangle: options.allowDrawRectangle !== undefined ? options.allowDrawRectangle : true,
      drawPolygon: options.allowDrawPolygon !== undefined ? options.allowDrawPolygon : true,
      drawCircle: false,
      drawText: false,
      cutPolygon: false,
      editMode: true,
      dragMode: false,
      removalMode: true,
      rotateMode: false,
    });

    map.pm.Toolbar.createCustomControl({
      name: 'Home',
      block: 'custom',
      title: 'Navigate to Home',
      toggle: false,
      className: 'custom-pm-icon-home',
      onClick: () => {
        this.goHome();
      },
    });

    // map.pm.Toolbar.createCustomControl({
    //   name: 'Paste',
    //   block: 'options',
    //   title: 'Paste Wizard',
    //   className: 'custom-pm-icon-brush',
    //   actions: [
    //     {
    //       text: 'Paste Bounding Box',
    //       onClick: () => {
    //         map.pm.Toolbar.buttons.Paste.toggle();
    //         // showModal();
    //       },
    //     },
    //     {
    //       text: 'Paste Point',
    //       onClick: () => {
    //         map.pm.Toolbar.buttons.Paste.toggle();
    //         alert('This feature is not yet implemented. Please draw a point manually or paste a GeoJSON feature using the "Draw" tools.');
    //       },
    //     },
    //   ],
    // });

    map.pm.Toolbar.changeControlOrder([
      'Home',
      'Paste',
    ]);

    drawnGroup = Leaflet.featureGroup().addTo(map);

    let drawnLayer;
    map.on('pm:create', (e) => {
      drawnLayer = e.layer;
      // Allow PM to manage the layer
      drawnLayer.options.pmIgnore = false;
      Leaflet.PM.reInitLayer(drawnLayer);
      drawnGroup.addLayer(drawnLayer);
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
      drawnGroup.clearLayers();
    });

    // Listen for the drawstart event and clear the previously drawn features, if any.
    map.on('pm:drawstart', () => {
      if (drawnLayer) {
        drawnLayer.off();
        drawnGroup.clearLayers();
        map.removeLayer(drawnLayer);
      }
    });

    Leaflet.control.layers(mapStyles).addTo(map);

    // this.features = Leaflet.featureGroup().addTo(this.map);
    // this.selectedFeatures = Leaflet.featureGroup().addTo(this.map);
    map.setView(INITIAL_CENTER, INITIAL_ZOOM);
  }

  getDrawnFeaturesAsGeoJSON() {
    return drawnGroup.toGeoJSON();
  }

  goHome() {
    map.setZoom(INITIAL_ZOOM, { animate: true });
    map.panTo(INITIAL_CENTER, { animate: true, duration: 1 });
  }

  on(eventName, callback) {
    return geoVizEventEmitter.on(eventName, callback);
  }

  fixMapSize() {
    setTimeout(() => {
      map.invalidateSize(true);
    }, 10);
  }

  clearMap() {
    drawnGroup.clearLayers();
  }
}
