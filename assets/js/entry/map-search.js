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
import 'devextreme/ui/text_box';
import 'devextreme/ui/button_group';
import CustomStore from 'devextreme/data/custom_store';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min';
import * as GeoViz from '../modules/geoViz-leaflet';

$(() => {
  const isNotEmpty = (v) => v != null && v !== '';

  const customDataSource = new CustomStore({
    key: 'udi',
    load(loadOptions) {
      const d = $.Deferred();
      const params = {};
      const customLoadOptions = { ...loadOptions };
      const dxButtonGroup = $('#geometry-method').dxButtonGroup('instance');
      customLoadOptions.userData.geometrySearchMode = dxButtonGroup.option('selectedItemKeys').toString();

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
        if (i in customLoadOptions && isNotEmpty(customLoadOptions[i])) {
          params[i] = JSON.stringify(customLoadOptions[i]);
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

  const treeList = $('#rg-tree').dxTreeList({
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
      visible: true,
    },
    columns: [{
      dataField: 'name',
      caption: 'Select All',
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
      let selectedItems = [];
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

      if (selectedItems.length === 0) {
        selectedItems = null;
      }

      const dataGrid = $('#datasets-grid').dxDataGrid('instance');
      dataGrid.columnOption('researchgroup', 'filterValue', selectedItems);
    },
  }).dxTreeList('instance');

  const popup = $('#rg-popup').dxPopup({
    width: 400,
    height: 600,
    visible: false,
    title: 'Organization Filter',
    hideOnOutsideClick: true,
    showCloseButton: false,
    showTitle: false,
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
      enabled: false,
      pageSize: 100,
    },
    scrolling: {
      mode: 'standard',
      rowRenderingMode: 'standard',
    },
    filterRow: { visible: false },
    filterPanel: { visible: false },
    pager: {
      visible: false,
      showInfo: true,
    },
    searchPanel: {
      visible: false,
      placeholder: 'Search...',
    },
    selection: {
      mode: 'single',
    },
    filterSyncEnabled: true,
    wordWrapEnabled: true,
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
    onContentReady(e) {
      const datasource = e.component.getDataSource();
      const filteredDatasets = [];
      const items = datasource.items();
      // change element btnItems value with items count
      $('#btnItems').text(`${items.length} Items`);
      if (e.component.getCombinedFilter() !== undefined) {
        items.forEach((dataset) => {
        // if dataset.geometery is undefined, skip it
          if (dataset.geometry === undefined) {
            return;
          }
          const geojson = JSON.parse(dataset.geometry);
          filteredDatasets.push(geojson);
        });
      }
      GeoViz.addToSelectedLayer(filteredDatasets);
    },

    onSelectionChanged(e) {
      if (e.currentDeselectedRowKeys.length > 0) {
        GeoViz.hideGeometryByUDI(e.currentDeselectedRowKeys[0]);
      }
      if (e.currentSelectedRowKeys.length > 0) {
        GeoViz.zoomAndPanToFeature(e.currentSelectedRowKeys[0]);
      }
    },
    onCellHoverChanged(e) {
      if (e.row && e.row.isSelected) {
        return;
      }
      if (e.eventType === 'mouseover') {
        if (e.data && e.data.udi) {
          GeoViz.showGeometryByUDI(e.data.udi);
        }
      } else if (e.eventType === 'mouseout') {
        if (e.data && e.data.udi) {
          GeoViz.hideGeometryByUDI(e.data.udi);
        }
      }
    },
  }).dxDataGrid('instance');

  $('#dg-toolbar').dxToolbar({
    items: [
      {
        location: 'before',
        widget: 'dxDateBox',
        options: {
          type: 'date',
          stylingMode: 'underlined',
          label: 'Start Date',
          labelMode: 'static',
          displayFormat: 'shortdate',
          placeholder: 'mm/dd/yyyy',
          showClearButton: true,
          elementAttr: {
            id: 'start-date',
          },
          onValueChanged(e) {
            let filter = null;
            if (e.value) {
              filter = e.value;
            }
            dataGrid.columnOption('collectionStartDate', 'filterValue', filter);
          },
        },
      },
      {
        location: 'before',
        widget: 'dxDateBox',
        options: {
          label: 'End Date',
          labelMode: 'static',
          stylingMode: 'underlined',
          type: 'date',
          displayFormat: 'shortdate',
          placeholder: 'mm/dd/yyyy',
          showClearButton: true,
          elementAttr: {
            id: 'end-date',
          },
          onValueChanged(e) {
            let filter = null;
            if (e.value) {
              filter = e.value;
            }
            dataGrid.columnOption('collectionEndDate', 'filterValue', filter);
          },
        },
      },
      {
        location: 'before',
        widget: 'dxButton',
        options: {
          elementAttr: {
            id: 'rg-select',
          },
          text: 'Organization Filter',
          onClick() {
            popup.show();
          },
        },
      },
      {
        location: 'before',
        widget: 'dxButtonGroup',
        options: {
          elementAttr: {
            id: 'geometry-method',
          },
          keyExpr: 'key',
          selectedItemKeys: ['within'],
          onSelectionChanged() {
            const geojson = dataGrid.columnOption('geometry', 'filterValue');
            if (geojson) {
              dataGrid.refresh();
            }
          },
          items: [
            {
              text: 'Within',
              key: 'within',
            },
            {
              text: 'Intersects',
              key: 'intersects',
            },
          ],
        },
      },
      {
        location: 'after',
        widget: 'dxButton',
        options: {
          text: 'Loading...',
          stylingMode: 'text',
          elementAttr: {
            id: 'btnItems',
          },
        },
      },
      {
        location: 'after',
        widget: 'dxButton',
        options: {
          text: 'Clear Filters',
          onClick() {
            dataGrid.clearFilter();
            dataGrid.deselectAll();
            $('#start-date').dxDateBox('instance').reset();
            $('#end-date').dxDateBox('instance').reset();
            $('#search-text').dxTextBox('instance').reset();
            treeList.deselectAll();
            treeList.searchByText('');
            treeList.forEachNode((node) => {
              treeList.collapseRow(node.key);
            });
            GeoViz.resetFeatures();
            popup.hide();
          },
        },
      },
      {
        location: 'after',
        widget: 'dxTextBox',
        options: {
          width: '240px',
          elementAttr: {
            id: 'search-text',
          },
          placeholder: 'Search...',
          showClearButton: true,
          onContentReady(e) {
            e.component.element().find('.dx-icon-clear').click(() => {
              dataGrid.clearFilter();
            });
          },
          onOptionChanged(e) {
            if (e.name === 'text') {
              const { value } = e;
              if (value === '') {
                dataGrid.clearFilter();
              }
              dataGrid.searchByText(value);
            }
          },
        },
      },
    ],
  }).dxToolbar('instance');

  const highlightRow = (id) => {
    const key = dataGrid.getRowIndexByKey(id);
    const element = dataGrid.getRowElement(key);

    $('tr.dx-data-row').removeClass('dx-selection');
    element.addClass('dx-selection');
    element.get(0).scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
  };

  GeoViz.on('geojsonupdated', (e) => {
    dataGrid.columnOption('geometry', 'filterValue', e.geojson);
  });

  GeoViz.on('featureselected', (e) => {
    const { id } = e.feature.properties;
    highlightRow(id);
  });
});
