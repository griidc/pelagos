$(() => {
  var selectedItem;

  const datasetFilters = [{
    id: 'all',
    text: 'Show All Datasets',
  }, {
    id: 'only',
    text: 'Only that have Datasets',
  }, {
    id: 'without',
    text: 'Those Without Datasets',
  }];

  var datasetFilter = datasetFilters[0].id;

  var groups = [{
    id: -1,
    name: "Loading...",
    expanded: true
  }];

  var groupStore;

  const loadGroups = () => {
    $.ajax({
      url: Routing.generate('app_api_dataset_monitoring_groups'),
      method: 'GET',
      dataType: 'json',
    }).then(function (response) {
      groupStore = new DevExpress.data.DataSource({
        paginate: false,
        store: {
          type: "array",
          key: "id",
          data: response
        },
      });
      groupStore.load().done(function (data) {
        groups = data;
      });
      dsmTreeList.option("dataSource", groups);
      dsmTreeList.repaint();
    }).always(function () {
      dsmTreeList.option("disabled", false);
    });
  }

  const filterGroups = (datasetFilter) => {
    switch (datasetFilter) {
      case 'only':
        dsmTreeList.filter(["datasets", ">", 0]);
        break;
      case 'without':
        dsmTreeList.filter(["datasets", "=", 0]);
        break;
      default:
        dsmTreeList.filter(null);
        break;
    }

    if (typeof selectedItem !== 'undefined') {
      loadGroupHtml(selectedItem);
    }
  }

  const collapseAll = () => {
    dsmTreeList.beginUpdate();
    dsmTreeList.forEachNode((node) => {
      dsmTreeList.collapseRow(node.key);
    })
    dsmTreeList.endUpdate();
  }

  const dsmToolbar = $('#dsm-toolbar').dxToolbar({
    items:
      [
        {
          location: 'before',
          widget: 'dxSelectBox',
          locateInMenu: 'never',
          options: {
            width: 'auto',
            items: datasetFilters,
            valueExpr: 'id',
            displayExpr: 'text',
            value: datasetFilters[0].id,
            onValueChanged(e) {
              datasetFilter = e.value;
              filterGroups(datasetFilter);
            },
          },
        },
        {
          location: 'after',
          widget: 'dxButton',
          locateInMenu: 'never',
          options: {
            hint: 'Collape All',
            icon: 'collapse',
            onClick() {
              collapseAll();
            },
          },
        },
      ]
  }).dxToolbar('instance');

  const dsmLoadPanel = $('.dsm-loadpanel').dxLoadPanel({
    visible: false,
    showIndicator: true,
    showPane: true,
    shading: false,
    hideOnOutsideClick: false,
  }).dxLoadPanel('instance');

  const dsmTreeList = $('#dsm-treelist').dxTreeList({
    dataSource: groups,
    keyExpr: 'id',
    parentIdExpr: 'parent',
    filterRow: {
      visible: true
    },
    headerFilter: {
      visible: false,
    },
    searchPanel: {
      visible: false,
    },
    columns: [{
      dataField: 'name',
      caption: 'Name',
      dataType: "string",
      filterOperations: ["reset"],
      selectedFilterOperation: "contains"
    },
    {
      dataType: "number",
      dataField: 'datasets',
      visible: false,
      allowSearch: false,
    }
    ],
    disabled: true,
    showRowLines: false,
    showBorders: false,
    showColumnHeaders: false,
    columnAutoWidth: false,
    wordWrapEnabled: true,
    selection: {
      mode: 'single',
    },
    onInitialized() {
      loadGroups();
    },
    onSelectionChanged(event) {
      selectedItem = event.selectedRowsData[0];
      loadGroupHtml(selectedItem);
    },
  }).dxTreeList('instance');

  const loadGroupHtml = (selectedItem) => {
    dsmLoadPanel.toggle(true);
    dsmTreeList.option("disabled", true);
    var parameters = { "datasetFilter": datasetFilter };
    if (selectedItem.fundingOrganization !== undefined) {
      parameters = Object.assign(parameters, { fundingOrganization: selectedItem.fundingOrganization });
    }
    if (selectedItem.fundingCycle !== undefined) {
      parameters = Object.assign(parameters, { fundingCycle: selectedItem.fundingCycle });
    }
    if (selectedItem.researchGroup !== undefined) {
      parameters = Object.assign(parameters, { researchGroup: selectedItem.researchGroup });
    }

    $.ajax({
      url: Routing.generate("app_api_dataset_monitoring_datasets",
        parameters
      ),
      method: "GET",
      accept: "text/html",
    }).then(function (html) {
      $("#dsm-datasets").html(html);
    }).done(function () {
      dsmLoadPanel.toggle(false);
      dsmTreeList.option("disabled", false);
    });
  };
});
