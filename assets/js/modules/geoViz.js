/* eslint-disable class-methods-use-this */
import * as Leaflet from 'leaflet';
import 'esri-leaflet';
import * as EsriLeafletVector from 'esri-leaflet-vector';
import '../../css/custom-pm-icons.css';
import '@geoman-io/leaflet-geoman-free';
import '@geoman-io/leaflet-geoman-free/dist/leaflet-geoman.css';
import { FullScreen } from 'leaflet.fullscreen';
import { EventEmitter } from 'events';
// import 'leaflet/dist/leaflet.css'; # This is broken due to webpack, but it is imported in the index.html.twig file.
import 'leaflet.fullscreen/dist/Control.FullScreen.css';

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

let drawnLayer = null;
let isFullScreen = false;
const drawnLayers = new Leaflet.FeatureGroup();
export default class GeoViz {
  constructor(element, options = {}) {
    const loadWizard = options.loadWizard !== undefined ? options.loadWizard : false;

    this.isFullScreen = () => isFullScreen;

    this.map = Leaflet.map(element, {
      preferCanvas: true,
      minZoom: 2,
      maxZoom: 14,
      attributionControl: true,
      worldCopyJump: true,
      layers: [ArcGISImagery],
    });

    drawnLayers.addTo(this.map);

    this.map.addControl(
      new FullScreen({
        position: 'topleft',
        forcePseudoFullscreen: true,
        title: 'Full screen',
        titleCancel: 'Exit full screen',
      }),
    );

    this.map.on('enterFullscreen', () => {
      isFullScreen = true;
    });

    this.map.on('exitFullscreen', () => {
      isFullScreen = false;
    });

    this.toggleFullScreen = () => {
      this.map.toggleFullscreen();
    };

    Leaflet.PM.setOptIn(true);

    const customTranslation = {
      tooltips: {
        placeCircleMarker: 'Click to draw point',
      },
      buttonTitles: {
        editButton: 'Edit feature(s)',
        deleteButton: 'Remove feature(s)',
        drawPolyButton: 'Draw polygon',
        drawRectButton: 'Draw bounding box',
        drawCircleMarkerButton: 'Draw point(s)',
      },
      actions: {
        removeLastVertex: 'Remove last vertex',
      },
    };

    this.map.pm.setLang('customText', customTranslation, 'en');

    this.map.pm.addControls({
      positions: {
        draw: 'topleft',
        edit: 'topleft',
        custom: 'topleft',
        options: 'topleft',
      },
      drawMarker: false,
      drawCircleMarker: false,
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

    this.map.pm.Toolbar.copyDrawControl('CircleMarker', {
      name: 'drawPoint',
      title: 'Draw point(s)',
      continueDrawing: true,
      afterClick: () => {
        this.map.pm.enableDraw('drawPoint', { continueDrawing: true });
      },
      actions: [
        {
          text: 'Done',
          onClick: () => {
            this.map.pm.disableDraw('drawPoint');
          },
        },
      ],
    });

    this.map.pm.Toolbar.createCustomControl({
      name: 'Home',
      block: 'custom',
      title: 'Home',
      toggle: false,
      className: 'custom-pm-icon-home',
      onClick: () => {
        this.goHome();
      },
    });

    this.map.pm.Toolbar.changeControlOrder([
      'Home',
    ]);

    if (loadWizard) {
      import('./spatialWizard').then((module) => {
        // eslint-disable-next-line new-cap
        const myWizard = new module.default(this);
        myWizard.init();
      });
    }

    this.map.on('pm:create', (e) => {
      drawnLayer = e.layer;
      drawnLayers.addLayer(drawnLayer);
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

    this.map.on('pm:update', (e) => {
      const updatedGeojson = e.layer.toGeoJSON();
      if (updatedGeojson) {
        geoVizEventEmitter.emit('geojsonupdated', { geojson: updatedGeojson, updated: true });
      }
    });

    this.map.on('pm:remove', ({ layer }) => {
      const updatedGeojson = layer.toGeoJSON();
      drawnLayers.removeLayer(layer);
      if (updatedGeojson) {
        geoVizEventEmitter.emit('geojsonupdated', { geojson: updatedGeojson, removed: true });
      }
    });

    ['pm:globaleditmodetoggled', 'pm:globalremovalmodetoggled'].forEach((eventName) => {
      this.map.on(eventName, () => {
        if (drawnLayer) {
          drawnLayer.bringToFront();
        }
      });
    });

    // Listen for the drawstart event and clear the previously drawn features, if any.
    this.map.on('pm:drawstart', () => {
      if (drawnLayer) {
        drawnLayer.off();
        drawnLayer.removeFrom(this.map);
        drawnLayers.clearLayers();
      }
    });

    Leaflet.control.layers(mapStyles).addTo(this.map);
    this.map.setView(INITIAL_CENTER, INITIAL_ZOOM);
  }

  getDrawnFeaturesAsGeoJSON() {
    return drawnLayers.toGeoJSON();
  }

  goHome() {
    this.map.setZoom(INITIAL_ZOOM, { animate: true });
    setTimeout(() => {
      this.map.panTo(INITIAL_CENTER, { animate: true, duration: 1 });
    }, 300);
  }

  on(eventName, callback) {
    return geoVizEventEmitter.on(eventName, callback);
  }

  fixMapSize() {
    setTimeout(() => {
      this.map.invalidateSize(true);
    }, 10);
  }

  clearMap() {
    drawnLayers.eachLayer((layer) => {
      layer.off();
      drawnLayers.removeLayer(layer);
    });
    drawnLayers.clearLayers();
  }

  addFeature(geojson) {
    this.clearMap();
    drawnLayer = Leaflet.geoJSON(geojson, {
      pmIgnore: false,
      pointToLayer: (feature, latlng) => Leaflet.circleMarker(latlng, { pmIgnore: false }),
    });
    drawnLayer.addTo(this.map);
    drawnLayers.addLayer(drawnLayer);
    this.map.fitBounds(drawnLayer.getBounds(), { animate: true, maxZoom: 6 });
  }
}
