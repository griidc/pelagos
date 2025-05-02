import '../../scss/map-search.scss';
import $ from 'jquery';

import 'devextreme/scss/bundles/dx.light.scss';
import 'devextreme/integration/jquery';
import 'devextreme/ui/data_grid';
import 'devextreme/ui/toolbar';
import 'devextreme/ui/button';
import 'devextreme/ui/date_box';
import 'devextreme/ui/select_box';
import 'devextreme/ui/tree_list';
import 'devextreme/ui/popup';
import CustomStore from 'devextreme/data/custom_store';

import * as Leaflet from 'leaflet';
import 'esri-leaflet';
import * as EsriLeafletVector from 'esri-leaflet-vector';
import '../../css/custom-pm-icons.css';
// import 'leaflet/dist/leaflet.css';
// import '../../css/leaflet-custom.css';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min';
import '@geoman-io/leaflet-geoman-free';
import '@geoman-io/leaflet-geoman-free/dist/leaflet-geoman.css';

const esriApiKey = process.env.ESRI_API_KEY;
const worldViewCode = process.env.WORLD_VIEW_CODE;

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

const map = Leaflet.map('leaflet-map', {
  preferCanvas: true,
  minZoom: 2,
  maxZoom: 14,
  attributionControl: true,
  worldCopyJump: true,
  layers: [ArcGISImagery],
});

function goHome() {
  map.setZoom(3, {
    animate: true,
  });
  map.panTo([27.5, -97.5], {
    animate: true,
    duration: 1,
  });
}

const styles = {
  'ArcGIS Imagery': ArcGISImagery,
  'ArcGIS Oceans': ArcGISOceans,
  'ArcGIS Terrain': ArcGISTerrain,
};

const controlLayer = Leaflet.control.layers(styles).addTo(map);

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
  'drawPolygon',
  'drawRectangle',
  'removalMode',
]);

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
  controlLayer.addOverlay(geojsonLayer, 'Show All Features');
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

function clearFeatures() {
  features.clearLayers();
}

function isNotEmpty(value) {
  return value !== undefined && value !== null && value !== '';
}

$(() => {
  const selectedGroups = [{
    id: 0,
    name: 'None Selected',
  }];

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

      const searchUrl = Routing.generate('app_map_search_search');

      $.getJSON(searchUrl, params)
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

  $('#rg-tree').dxTreeList({
    dataSource: Routing.generate('app_api_dataset_monitoring_groups'),
    keyExpr: 'id',
    parentIdExpr: 'parent',
    filterRow: {
      visible: false,
    },
    headerFilter: {
      visible: false,
    },
    searchPanel: {
      visible: false,
    },
    columns: [{
      dataField: 'name',
      caption: 'Research Group',
      dataType: 'string',
    },
    {
      dataType: 'number',
      dataField: 'datasets',
      visible: false,
      allowSearch: false,
    },
    ],
    disabled: false,
    showRowLines: false,
    showBorders: false,
    showColumnHeaders: true,
    columnAutoWidth: false,
    wordWrapEnabled: true,
    selection: {
      allowSelectAll: true,
      mode: 'multiple',
      recursive: true,
    },
    onSelectionChanged(e) {
      const selectedItems = [];
      e.selectedRowsData.forEach((item) => {
        const { researchGroup } = item;
        if (Array.isArray(researchGroup)) {
          item.researchGroup.forEach((group) => {
            selectedItems.push(group);
          });
        } else {
          selectedItems.push(researchGroup);
        }
      });

      const dataGrid = $('#datasets-grid').dxDataGrid('instance');
      dataGrid.columnOption('researchgroup', 'filterValue', selectedItems);
    },
  });

  const popup = $('#rg-popup').dxPopup({
    width: 400,
    height: 600,
    visible: false,
    title: 'Organization Filter',
    hideOnOutsideClick: true,
    showCloseButton: true,
    position: {
      my: 'top',
      at: 'top',
      of: '#rg-select',
    },
    toolbarItems: [
      {
        widget: 'dxButton',
        toolbar: 'bottom',
        location: 'center',
        options: {
          text: 'Reset',
          type: 'default',
          stylingMode: 'contained',
          onClick() {
            const treeList = $('#rg-tree').dxTreeList('instance');
            treeList.deselectAll();
            treeList.forEachNode((node) => {
              treeList.collapseRow(node.key);
            });
          },
        },
      },
      {
        widget: 'dxButton',
        toolbar: 'bottom',
        location: 'center',
        options: {
          text: 'Close',
          type: 'default',
          stylingMode: 'contained',
          onClick() {
            popup.hide();
          },
        },
      },
    ],
  }).dxPopup('instance');

  const dataGrid = $('#datasets-grid').dxDataGrid({
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
          visible: false,
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
          visible: false,
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
          options: {
            type: 'date',
            displayFormat: 'shortdate',
            placeholder: 'mm/dd/yyyy',
            showClearButton: true,
            onValueChanged(e) {
              let filter = null;
              if (e.value) {
                filter = e.value;
              }
              // const dataGrid = $('#datasets-grid').dxDataGrid('instance');
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
            placeholder: 'mm/dd/yyyy',
            showClearButton: true,
            onValueChanged(e) {
              let filter = null;
              if (e.value) {
                filter = e.value;
              }
              // const dataGrid = $('#datasets-grid').dxDataGrid('instance');
              dataGrid.columnOption('collectionEndDate', 'filterValue', filter);
            },
          },
        },
        {
          location: 'before',
          widget: 'dxButton',
          visible: false,
          options: {
            icon: 'clear',
            onClick() {
              $('#datasets-grid').dxDataGrid('instance').clearSelection();
              clearFeatures();
            },
          },
        },
        {
          location: 'before',
          widget: 'dxSelectBox',
          options: {
            elementAttr: {
              id: 'rg-select',
            },
            dataSource: selectedGroups,
            value: 0,
            width: '14em',
            displayExpr: 'name',
            valueExpr: 'id',
            placeholder: 'Select Research Group',
            onOpened(e) {
              popup.show();
              e.component.close();
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
      {
        id: 'researchgroup',
        name: 'researchgroup',
        dataField: 'researchGroup.id',
        visible: false,
        allowSearch: false,
        allowHeaderFiltering: true,
        dataType: 'number',
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
  }).dxDataGrid('instance');
});
