$(() => {
  var selectedItem;

  const datasetFilters = [{
    id: 'all',
    text: 'Show All Datasets',
  }, {
    id: 'only',
    text: 'Show Groups With Datasets',
  }, {
    id: 'without',
    text: 'Show Groups Without Datasets',
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
      const expandedKeys = groupStore.items().filter(({ expanded }) => expanded === true).map(key => key.id);

      // Get query parameters from URL and store on respective variables
      const urlParams = new URLSearchParams(window.location.search);
      const fundingOrganization = urlParams.get('fundingOrganization');
      const fundingCycle = urlParams.get('fundingCycle');
      const researchGroup = urlParams.get('researchGroup');

      // if any of the query parameters is present, expand the respective groups
      if (fundingOrganization) {
        const fundingOrganizationItem = groups.find(group => group.fundingOrganization === parseInt(fundingOrganization, 10));
        expandedKeys.push(fundingOrganizationItem.id);
        dsmTreeList.selectRows([fundingOrganizationItem.id], false);
      }

      if (researchGroup) {
        const researchGroupItem = groups.find(group => group.researchGroup === parseInt(researchGroup, 10));
        const parentItem = groups.find(group => group.id === researchGroupItem.parent);
        expandedKeys.push(researchGroupItem.parent);
        expandedKeys.push(parentItem.parent);
        dsmTreeList.selectRows([researchGroupItem.id], false);
      }

      if (fundingCycle) {
        const fundingCycleItem = groups.find(group => group.fundingCycle === parseInt(fundingCycle, 10));
        expandedKeys.push(fundingCycleItem.id);
        expandedKeys.push(fundingCycleItem.parent);
        dsmTreeList.selectRows([fundingCycleItem.id], false);
      }

      dsmTreeList.option("expandedRowKeys", expandedKeys);
    });
  }

  const filterGroups = (datasetFilter) => {
    var filterArray = null;
    switch (datasetFilter) {
      case 'only':
        filterArray = [["datasets", ">", 0]];
        break;
      case 'without':
        filterArray = [["datasets", "=", 0]];
        break;
      default:
        break;
    }

    const searchValue = dsmSearch.option("value");
    if (searchValue.length > 3) {
      var searchArray = [["name", "contains", searchValue], "or", ["list", "contains", searchValue]];
      if (filterArray !== null) {
        filterArray.push("and");
        filterArray.push(searchArray);
      } else {
        filterArray = searchArray;
      }
    }

    dsmTreeList.filter(filterArray);

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
            dropDownOptions: {
              minWidth: '16rem',
            },
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

  const dsmSearch = $('#dsm-search').dxTextBox({
    placeholder: 'Search Groups',
    showClearButton: true,
    mode: 'search',
    valueChangeEvent: ["keyup", "blur", "change", "input"],
    onValueChanged() {
      filterGroups(datasetFilter);
    },
  }).dxTextBox('instance');

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
      visible: false
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
    if (selectedItem.researchGroup !== undefined && !Array.isArray(selectedItem.researchGroup))
    {
      parameters = Object.assign(parameters, { researchGroup: selectedItem.researchGroup });
    }

    // Change current browser URL to add query parameters
    var currentUrl = new URL(window.location.href);
    var queryParams = new URLSearchParams();
    var urlPararameters = Object.assign({}, parameters);
    delete urlPararameters.datasetFilter;
    for (const key of Object.keys(urlPararameters)) {
      queryParams.set(key, urlPararameters[key]);
    }
    currentUrl.search = queryParams.toString();
    window.history.pushState({}, '', currentUrl);

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
