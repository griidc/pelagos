import '../../scss/map-search.scss';
import $ from 'jquery';
import 'devextreme/integration/jquery';
import 'devextreme/ui/data_grid';
import 'devextreme/ui/toolbar';
import 'devextreme/ui/button';
import 'devextreme/ui/date_box';
import 'devextreme/scss/bundles/dx.light.scss';
import CustomStore from 'devextreme/data/custom_store';

import * as Leaflet from 'leaflet';
import 'esri-leaflet';
import * as EsriLeafletVector from 'esri-leaflet-vector';
// import 'leaflet/dist/leaflet.css';
// import '../../css/leaflet-custom.css';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min';
import '@geoman-io/leaflet-geoman-free';
import '@geoman-io/leaflet-geoman-free/dist/leaflet-geoman.css';

const esriApiKey = process.env.ESRI_API_KEY;

const GRIIDCStyle = {
  color: 'orange',
  weight: 4,
  opacity: 1,
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
  editMode: false,
  dragMode: false,
  removalMode: true,
  rotateMode: false,
});

let drawnLayer;
// Function to handle the map filter drawn event
map.on('pm:create', (e) => {
  drawnLayer = e.layer;
  const geojson = drawnLayer.toGeoJSON();
  if (geojson) {
    const dataGrid = $('#datasets-grid').dxDataGrid('instance');
    dataGrid.columnOption('geometry', 'filterValue', geojson);
  }
});

map.on('pm:remove', () => {
  const dataGrid = $('#datasets-grid').dxDataGrid('instance');
  dataGrid.columnOption('geometry', 'filterValue', null);
});

// Listen for the drawstart event and clear the previously drawn features, if any.
map.on('pm:drawstart', () => {
  if (drawnLayer) {
    map.removeLayer(drawnLayer);
  }
});

let basemapEnum = 'ArcGIS:Imagery';

const basemapOptions = {

  'ArcGIS:Imagery': () => EsriLeafletVector.vectorBasemapLayer('ArcGIS:Imagery', { apiKey: esriApiKey }).addTo(map),

  'ArcGIS:Oceans': () => EsriLeafletVector.vectorBasemapLayer('ArcGIS:Oceans', { apiKey: esriApiKey }).addTo(map),

  'USGS_USImageryTopo': () => Leaflet.tileLayer('https://basemap.nationalmap.gov/arcgis/rest/services/USGSImageryTopo/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Tiles courtesy of the <a href="https://usgs.gov/">U.S. Geological Survey</a>'
  }).addTo(map),

  'Esri_NatGeoWorldMap': () => Leaflet.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/NatGeo_World_Map/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Tiles &copy; Esri &mdash; National Geographic, Esri, DeLorme, NAVTEQ, UNEP-WCMC, USGS, NASA, ESA, METI, NRCAN, GEBCO, NOAA, iPC',
  }).addTo(map),

  'OpenStreetMap': () => Leaflet.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
  }).addTo(map),

  'OpenTopoMap': () => Leaflet.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
    attribution: 'Map data: &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, <a href="http://viewfinderpanoramas.org">SRTM</a> | Map style: &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)'
  }).addTo(map),

};

// Function to switch basemaps
function switchBasemap(basemap) {
  map.eachLayer((layer) => {
    if (layer !== features) {
      map.removeLayer(layer);
    }
  });
  basemapOptions[basemap]();
}

// Add a dropdown to switch basemaps
const basemapControl = Leaflet.control({ position: 'topright' });
basemapControl.onAdd = () => {
  const div = Leaflet.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');
  div.innerHTML = `
    <select id="basemap-selector">
      <option value="ArcGIS:Imagery">ArcGIS Imagery</option>
      <option value="ArcGIS:Oceans">ArcGIS Oceans</option>
      <option value="USGS_USImageryTopo">USGS US Imagery Topo</option>
      <option value="Esri_NatGeoWorldMap">Esri NatGeo World Map</option>
      <option value="OpenStreetMap">OpenStreetMap</option>
      <option value="OpenTopoMap">Open Topo Map</option>
    </select>
  `;
  div.style.padding = '5px';
  div.style.background = 'white';
  div.style.cursor = 'pointer';
  Leaflet.DomEvent.disableClickPropagation(div);
  return div;
};
basemapControl.addTo(map);

// Event listener for basemap selection
document.getElementById('basemap-selector').addEventListener('change', (e) => {
  basemapEnum = e.target.value;
  switchBasemap(basemapEnum);
});

// Initialize the default basemap
basemapOptions[basemapEnum]();
EsriLeafletVector.vectorBasemapLayer(basemapEnum, {
  apiKey: esriApiKey,
}).addTo(map);

const features = Leaflet.featureGroup().addTo(map);
map.setView([27.5, -97.5], 3);

let geojsonLayer = null;

const url = `${Routing.generate('pelagos_map_all_geojson')}`;
fetch(url).then((response) => response.json()).then((response) => {
  geojsonLayer = Leaflet.geoJSON(response, {
    pointToLayer(feature, latlng) {
      return Leaflet.circleMarker(latlng, geojsonMarkerOptions);
    },
    onEachFeature(feature, layer) {
      layer.bindTooltip(feature.properties.name.toString(), { permanent: false, className: 'label' });
    },
    style: GRIIDCStyle,
  });
});

function showGeometryByUDI(id) {
  if (geojsonLayer === null) {
    return;
  }
  geojsonLayer.eachLayer((layer) => {
    const { feature } = layer;
    if (feature.properties.id === id) {
      layer.bindTooltip(feature.properties.name.toString(), { permanent: true, className: 'label' });
      if (!features.hasLayer(layer)) {
        features.addLayer(layer);
      }
    }
  });
}

function hideGeometryByUDI(id) {
  if (geojsonLayer === null) {
    return;
  }
  geojsonLayer.eachLayer((layer) => {
    const { feature } = layer;
    if (feature.properties.id === id) {
      layer.bindTooltip(feature.properties.name.toString(), { permanent: false, className: 'label' });
      features.removeLayer(layer);
    }
  });
}

function zoomAndPanToFeature(id) {
  if (geojsonLayer === null) {
    return;
  }
  geojsonLayer.eachLayer((layer) => {
    if (layer.feature.properties.id === id) {
      features.addLayer(layer);
      layer.bringToFront();
      map.fitBounds(layer.getBounds(), { padding: [20, 20] });
    }
  });
}

function showAllGeometry() {
  if (geojsonLayer === null) {
    return;
  }
  geojsonLayer.eachLayer((layer) => {
    layer.bindTooltip(null);
    features.addLayer(layer);
  });
}

function hideAllGeometry() {
  if (geojsonLayer === null) {
    return;
  }
  geojsonLayer.eachLayer((layer) => {
    features.removeLayer(layer);
  });
}

function goHome() {
  map.setView([27.5, -97.5], 3);
}

function clearFeatures() {
  features.clearLayers();
}

function isNotEmpty(value) {
  return value !== undefined && value !== null && value !== '';
}

$(() => {
  const customDataSource = new CustomStore({
    key: 'udi',
    load(loadOptions) {
      const d = $.Deferred();
      const params = {};

      [
        'filter',
        'group',
        'groupSummary',
        'parentIds',
        'requireGroupCount',
        'requireTotalCount',
        'searchExpr',
        'searchOperation',
        'searchValue',
        'select',
        'sort',
        'skip',
        'take',
        'totalSummary',
        'userData',
      ].forEach((i) => {
        if (i in loadOptions && isNotEmpty(loadOptions[i])) {
          params[i] = JSON.stringify(loadOptions[i]);
        }
      });

      $.getJSON('/map/search', params)
        .done((response) => {
          d.resolve(response.data, {
            totalCount: response.totalCount,
            summary: response.summary,
            groupCount: response.groupCount,
          });
        })
        .fail(() => { d.reject(new Error('Data loading error')); });
      return d.promise();
    },
  });

  $('#datasets-grid').dxDataGrid({
    dataSource: customDataSource,
    remoteOperations: {
      groupPaging: true,
    },
    showBorders: true,
    showColumnLines: true,
    showRowLines: true,
    paging: {
      enabled: true,
      pageSize: 18,
    },
    filterRow: { visible: false },
    filterPanel: { visible: false },
    pager: {
      visible: true,
      showInfo: true,
    },
    searchPanel: {
      visible: true,
      placeholder: 'Search...',
    },
    selection: {
      mode: 'single',
    },
    filterSyncEnabled: true,
    toolbar: {
      items: [
        {
          location: 'before',
          widget: 'dxButton',
          options: {
            text: 'Show All',
            onClick() {
              if (this.option('text') === 'Hide All') {
                hideAllGeometry();
                this.option('text', 'Show All');
              } else if (this.option('text') === 'Show All') {
                showAllGeometry();
                this.option('text', 'Hide All');
              }
            },
          },
        },
        {
          location: 'before',
          widget: 'dxButton',
          options: {
            icon: 'home',
            onClick() {
              goHome();
            },
          },
        },
        {
          location: 'before',
          template: '<div>Start Date:</div>',
        },
        {
          location: 'before',
          widget: 'dxDateBox',
          type: 'date',
          displayFormat: 'shortdate',
          options: {
            showClearButton: true,
            onValueChanged(e) {
              let filter = null;
              if (e.value) {
                filter = e.value;
              }
              const dataGrid = $('#datasets-grid').dxDataGrid('instance');
              dataGrid.columnOption('collectionStartDate', 'filterValue', filter);
            },
          },
        },
        {
          location: 'before',
          template: '<div>End Date:</div>',
        },
        {
          location: 'before',
          widget: 'dxDateBox',
          options: {
            type: 'date',
            displayFormat: 'shortdate',
            showClearButton: true,
            onValueChanged(e) {
              let filter = null;
              if (e.value) {
                filter = e.value;
              }
              const dataGrid = $('#datasets-grid').dxDataGrid('instance');
              dataGrid.columnOption('collectionEndDate', 'filterValue', filter);
            },
          },
        },
        {
          location: 'before',
          widget: 'dxButton',
          options: {
            icon: 'clear',
            onClick() {
              $('#datasets-grid').dxDataGrid('instance').clearSelection();
              clearFeatures();
            },
          },
        },
        'searchPanel',
      ],
    },
    headerFilter: {
      visible: true,
    },
    columns: [
      {
        dataField: 'udi',
        caption: 'UDI',
        width: 162,
        allowHeaderFiltering: false,
        allowSorting: true,
      },
      {
        dataField: 'doi.doi',
        caption: 'DOI',
        width: 201,
        allowHeaderFiltering: false,
        allowSorting: false,
        cellTemplate(container, options) {
          const doiurl = `https://doi.org/${options.value}`;
          if (!['Identified', 'None'].includes(options.data.status)) {
            return $('<a>', { href: doiurl, target: '_blank', class: 'pagelink' }).text(options.displayValue);
          }
          return '';
        },
      },
      {
        dataField: 'title',
        caption: 'Title',
        allowHeaderFiltering: false,
        allowSorting: false,
        cellTemplate(container, options) {
          const dlurl = Routing.generate('pelagos_app_ui_dataland_default', { udi: options.data.udi });
          return $('<a>', { href: dlurl, target: '_blank', class: 'pagelink' }).text(options.displayValue);
        },
      },
      {
        dataField: 'datasetLifecycleStatus',
        caption: 'Status',
        width: 100,
        allowHeaderFiltering: true,
        allowSearch: false,
      },
      {
        id: 'collectionStartDate',
        name: 'collectionStartDate',
        dataField: 'collectionStartDate',
        visible: false,
        allowSearch: false,
        allowHeaderFiltering: true,
        dataType: 'date',
        selectedFilterOperation: '>=',
        filterOperations: ['>='],
      },
      {
        id: 'collectionEndDate',
        name: 'collectionEndDate',
        dataField: 'collectionEndDate',
        visible: false,
        allowSearch: false,
        allowHeaderFiltering: true,
        dataType: 'date',
        selectedFilterOperation: '<=',
        filterOperations: ['<='],
      },
      {
        id: 'geometry',
        name: 'geometry',
        dataField: 'geometry',
        visible: false,
        allowSearch: false,
        allowHeaderFiltering: true,
        dataType: 'string',
        selectedFilterOperation: '=',
        filterOperations: ['='],
      },
    ],
    hoverStateEnabled: true,
    onSelectionChanged(e) {
      if (e.currentDeselectedRowKeys.length > 0) {
        hideGeometryByUDI(e.currentDeselectedRowKeys[0]);
      }
      if (e.currentSelectedRowKeys.length > 0) {
        zoomAndPanToFeature(e.currentSelectedRowKeys[0]);
      }
    },
    onCellHoverChanged(e) {
      if (e.row && e.row.isSelected) {
        return;
      }
      if (e.eventType === 'mouseover') {
        if (e.data && e.data.udi) {
          showGeometryByUDI(e.data.udi);
        }
      } else if (e.eventType === 'mouseout') {
        if (e.data && e.data.udi) {
          hideGeometryByUDI(e.data.udi);
        }
      }
    },
  });
});
