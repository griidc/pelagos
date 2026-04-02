/* eslint-disable class-methods-use-this */
import * as Leaflet from 'leaflet';
import 'esri-leaflet';
import * as EsriLeafletVector from 'esri-leaflet-vector';
import '../../css/custom-pm-icons.css';
import '@geoman-io/leaflet-geoman-free';
import '@geoman-io/leaflet-geoman-free/dist/leaflet-geoman.css';
import { EventEmitter } from 'events';
// import 'leaflet/dist/leaflet.css'; # This is broken due to webpack, but it is imported in the index.html.twig file.

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
    // this.options = options;
    // this.element = element;
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
      position: 'topleft',
      drawCircleMarker: options.allowDrawPoint !== undefined ? options.allowDrawPoint : true,
      drawMarker: false,
      drawPolyline: options.allowDrawPolyline !== undefined ? options.allowDrawPolyline : true,
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
      className: 'custom-pm-icon-home',
      onClick: () => {
        this.goHome();
      },
    });

    map.pm.Toolbar.changeControlOrder([
      'Home',
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
    });

    // Listen for the drawstart event and clear the previously drawn features, if any.
    map.on('pm:drawstart', () => {
      if (drawnLayer) {
        drawnLayer.off();
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
}

// import * as Leaflet from 'leaflet';
// import 'esri-leaflet';
// import * as EsriLeafletVector from 'esri-leaflet-vector';
// import '../../css/custom-pm-icons.css';
// import '@geoman-io/leaflet-geoman-free';
// import '@geoman-io/leaflet-geoman-free/dist/leaflet-geoman.css';
// import { EventEmitter } from 'events';
// import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min';
// // import 'leaflet/dist/leaflet.css'; # This is broken due to webpack, but it is imported in the index.html.twig file.

// const esriApiKey = process.env.ESRI_API_KEY;
// const worldViewCode = process.env.WORLD_VIEW_CODE;
// const INITIAL_ZOOM = 3;
// const INITIAL_CENTER = [27.5, -97.5];

// const styles = {
//   defaultStyle:
//   {
//     color: '#fcaf08',
//     opacity: 1,
//     fillOpacity: 0,
//   },
//   selectedStyle:
//   {
//     color: '#08fcaf',
//     opacity: 1,
//     fillOpacity: 0,
//   },
//   hoverStyle:
//   {
//     color: '#af08fc',
//     opacity: 1,
//     fillOpacity: 0,
//   },
//   markerStyle:
//   {
//     radius: 6,
//     fill: false,
//     opacity: 1,
//   },
// };

// function getV2BasemapLayer(style) {
//   return EsriLeafletVector.vectorBasemapLayer(style, {
//     token: esriApiKey,
//     version: 2,
//     worldview: worldViewCode,
//   });
// }

// const ArcGISImagery = getV2BasemapLayer('arcgis/imagery');
// const ArcGISOceans = getV2BasemapLayer('arcgis/oceans');
// const ArcGISTerrain = getV2BasemapLayer('arcgis/terrain');

// const mapStyles = {
//   'ArcGIS Imagery': ArcGISImagery,
//   'ArcGIS Oceans': ArcGISOceans,
//   'ArcGIS Terrain': ArcGISTerrain,
// };

// class GeoVizLeaflet {
//   constructor(element = 'leaflet-map') {
//     this.element = element;
//     this.geoVizEventEmitter = new EventEmitter();
//     this.drawnLayer = null;
//     this.geojsonLayer = null;

//     this.map = Leaflet.map(this.element, {
//       preferCanvas: true,
//       minZoom: 2,
//       maxZoom: 14,
//       attributionControl: true,
//       worldCopyJump: true,
//       layers: [ArcGISImagery],
//     });

//     this.controlLayer = Leaflet.control.layers(mapStyles).addTo(this.map);

//     // Opt out for all layers for PM controls, so no layers can be edited by default
//     Leaflet.PM.setOptIn(true);

//     // Add Leaflet-Geoman controls with some options to the map
//     this.map.pm.addControls({
//       position: 'topleft',
//       drawCircleMarker: false,
//       drawMarker: false,
//       drawPolyline: false,
//       drawRectangle: true,
//       drawPolygon: true,
//       drawCircle: false,
//       drawText: false,
//       cutPolygon: false,
//       editMode: true,
//       dragMode: false,
//       removalMode: true,
//       rotateMode: false,
//     });

//     this.map.pm.Toolbar.createCustomControl({
//       name: 'Home',
//       block: 'custom',
//       title: 'Navigate to Home',
//       className: 'custom-pm-icon-home',
//       onClick: () => {
//         this.goHome();
//       },
//     });

//     // Change the order of the controls in the toolbar
//     this.map.pm.Toolbar.changeControlOrder([
//       'Home',
//     ]);

//     // Function to handle the map filter drawn event
//     this.map.on('pm:create', (e) => {
//       this.drawnLayer = e.layer;
//       // Allow PM to manage the layer
//       this.drawnLayer.options.pmIgnore = false;
//       Leaflet.PM.reInitLayer(this.drawnLayer);
//       const geojson = this.drawnLayer.toGeoJSON();
//       if (geojson) {
//         this.geoVizEventEmitter.emit('geojsonupdated', { geojson });
//       }
//       this.drawnLayer.on('pm:disable', (event) => {
//         const editedGeojson = event.target.toGeoJSON();
//         if (editedGeojson) {
//           this.geoVizEventEmitter.emit('geojsonupdated', { geojson: editedGeojson });
//         }
//       });
//     });

//     ['pm:globaleditmodetoggled', 'pm:globalremovalmodetoggled'].forEach((eventName) => {
//       this.map.on(eventName, () => {
//         if (this.drawnLayer) {
//           this.drawnLayer.bringToFront();
//         }
//       });
//     });

//     this.map.on('pm:remove', () => {
//       this.geoVizEventEmitter.emit('geojsonupdated', { geojson: null });
//     });

//     // Listen for the drawstart event and clear the previously drawn features, if any.
//     this.map.on('pm:drawstart', () => {
//       if (this.drawnLayer) {
//         this.drawnLayer.off();
//         this.map.removeLayer(this.drawnLayer);
//       }
//     });

//     this.features = Leaflet.featureGroup().addTo(this.map);
//     this.selectedFeatures = Leaflet.featureGroup().addTo(this.map);
//     this.map.setView(INITIAL_CENTER, INITIAL_ZOOM);

//     // Fetch all geojson features and add them to the map
//     const url = `${Routing.generate('pelagos_map_all_geojson')}`;
//     fetch(url).then((response) => response.json()).then((response) => {
//       this.geojsonLayer = Leaflet.geoJSON(response, {
//         pointToLayer(feature, latlng) {
//           return Leaflet.circleMarker(latlng, styles.markerStyle);
//         },
//         onEachFeature(feature, layer) {
//           layer.bindTooltip(feature.properties.name.toString(), { permanent: false, className: 'label' });
//           layer.on('mouseover', (e) => e.target.setStyle(styles.hoverStyle));
//           layer.on('mouseout', (e) => e.target.setStyle(styles.defaultStyle));
//         },
//         style: styles.defaultStyle,
//         markersInheritOptions: true,
//       });
//       this.controlLayer.addOverlay(this.geojsonLayer, 'Show All Features');
//     });
//   }

//   goHome() {
//     this.map.setZoom(INITIAL_ZOOM, { animate: true });
//     this.map.panTo(INITIAL_CENTER, { animate: true, duration: 1 });
//   }

//   /**
//    * Adds the given list of geojson features to the selected layer on the map.
//    *
//    * @param {*} list An array of geojson features to add to the selected layer.
//    * @returns void
//    */
//   addToSelectedLayer(list) {
//     this.selectedFeatures.clearLayers();
//     this.controlLayer.removeLayer(this.selectedFeatures);

//     list.forEach((geojson) => {
//       Leaflet.geoJSON(geojson, {
//         pointToLayer: (feature, latlng) => Leaflet.circleMarker(latlng, styles.markerStyle),
//         style: { color: '#08fcaf', opacity: 1, fillOpacity: 0 },
//         markersInheritOptions: true,
//         onEachFeature: (feature, layer) => {
//           layer.bindTooltip(feature.properties.name.toString(), { permanent: false, className: 'label' });
//           layer.on('click', (e) => this.geoVizEventEmitter.emit('featureselected', { feature: e.target.feature }));
//           layer.on('mouseover', (e) => e.target.setStyle(styles.hoverStyle));
//           layer.on('mouseout', (e) => e.target.setStyle(styles.selectedStyle));
//         },
//       }).addTo(this.selectedFeatures);
//     });

//     if (this.selectedFeatures.getLayers().length) {
//       this.controlLayer.addOverlay(this.selectedFeatures, 'Selected Features');
//       this.map.fitBounds(this.selectedFeatures.getBounds(), { padding: [20, 20] });
//     } else {
//       this.goHome();
//     }
//   }

//   /**
//    * Resets the features layer and removes the drawn layer from the map.
//    */
//   resetFeatures() {
//     this.features.clearLayers();
//     this.selectedFeatures.clearLayers();
//     if (this.drawnLayer) {
//       this.map.removeLayer(this.drawnLayer);
//     }
//     this.goHome();
//   }

//   /**
//    * Shows the geometry with the given id on the map.
//    *
//    * @param {*} id The id of the feature to show, usually the UDI.
//    * @returns void
//    */
//   showGeometryByUDI(id) {
//     if (!this.geojsonLayer) return;
//     this.geojsonLayer.eachLayer((layer) => {
//       if (layer.feature.properties.id === id) {
//         layer.bindTooltip(layer.feature.properties.name.toString(), { permanent: true, className: 'label' });
//         layer.setStyle(styles.hoverStyle);
//         if (!this.features.hasLayer(layer)) this.features.addLayer(layer);
//       }
//     });
//   }

//   /**
//    * Hides the geometry with the given id from the map.
//    *
//    * @param {*} id The id of the feature to hide.
//    * @returns void
//    */
//   hideGeometryByUDI(id) {
//     if (!this.geojsonLayer) return;
//     this.geojsonLayer.eachLayer((layer) => {
//       const { feature } = layer;
//       if (feature.properties.id === id) {
//         layer.bindTooltip(feature.properties.name.toString(), { permanent: false, className: 'label' });
//         this.features.removeLayer(layer);
//       }
//     });
//   }

//   /**
//    * Zooms and pans the map to the feature with the given id.
//    *
//    * @param {*} id The id of the feature to zoom and pan to.
//    * @returns void
//    */
//   zoomAndPanToFeature(id) {
//     if (!this.geojsonLayer) return;
//     this.geojsonLayer.eachLayer((layer) => {
//       if (layer.feature.properties.id === id) {
//         this.features.addLayer(layer);
//         layer.bringToFront();
//         this.map.fitBounds(layer.getBounds(), { padding: [20, 20] });
//       }
//     });
//   }

//   on(eventName, callback) {
//     this.geoVizEventEmitter.on(eventName, callback);
//   }

//   off(eventName, callback) {
//     this.geoVizEventEmitter.off(eventName, callback);
//   }

//   once(eventName, callback) {
//     this.geoVizEventEmitter.once(eventName, callback);
//   }
// }

// let geoViz = new GeoVizLeaflet();

// const initialize = (element = 'leaflet-map') => {
//   geoViz = new GeoVizLeaflet(element);
//   return geoViz;
// };

// const addToSelectedLayer = (list) => geoViz.addToSelectedLayer(list);
// const resetFeatures = () => geoViz.resetFeatures();
// const showGeometryByUDI = (id) => geoViz.showGeometryByUDI(id);
// const hideGeometryByUDI = (id) => geoViz.hideGeometryByUDI(id);
// const zoomAndPanToFeature = (id) => geoViz.zoomAndPanToFeature(id);
// const on = (eventName, callback) => geoViz.on(eventName, callback);
// const off = (eventName, callback) => geoViz.off(eventName, callback);
// const once = (eventName, callback) => geoViz.once(eventName, callback);

// export {
//   GeoVizLeaflet,
//   initialize,
//   addToSelectedLayer,
//   resetFeatures,
//   showGeometryByUDI,
//   hideGeometryByUDI,
//   zoomAndPanToFeature,
//   on,
//   off,
//   once,
// };

