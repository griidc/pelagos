import '../../scss/map-search.scss';
import $ from 'jquery';
import 'devextreme/integration/jquery';
import 'devextreme/ui/data_grid';
import 'devextreme/ui/toolbar';
import 'devextreme/ui/button';
import 'devextreme/scss/bundles/dx.light.scss';
import CustomStore from 'devextreme/data/custom_store';

import * as Leaflet from 'leaflet';
import 'esri-leaflet';
import * as EsriLeafletVector from 'esri-leaflet-vector';
// import 'leaflet/dist/leaflet.css';
import '../../css/leaflet-custom.css';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min';

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

const basemapEnum = 'ArcGIS:Imagery';
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
    // Needed to process selected value(s) in the SelectBox, Lookup, Autocomplete, and DropDownBox
    // byKey: function(key) {
    //     var d = new $.Deferred();
    //     $.get('/map/getkeys?id=' + key)
    //         .done(function(result) {
    //             d.resolve(result);
    //         });
    //     return d.promise();
    // }
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
      pageSize: 20,
    },
    pager: {
      visible: true,
      showInfo: true,
    },
    searchPanel: {
      visible: true,
      placeholder: 'Search...',
      width: 400,
    },
    selection: {
      mode: 'single',
    },
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
        allowSearching: false,
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
