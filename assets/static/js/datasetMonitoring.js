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

  const loadGroups = () =>
  {
    $.ajax({
      url: Routing.generate('app_api_dataset_monitoring_groups'),
      method: 'GET',
      dataType : 'json',
    }).then(function (response) {
      groupStore = new DevExpress.data.DataSource({
        paginate: false,
        store: {
          type: "array",
          key: "id",
          data: response
        },
      });
      filterGroups(datasetFilter);
    }).always(function() {
      dsmTreeview.option("disabled", false);
    });
  }

  const filterGroups = (datasetFilter) =>
  {
    switch (datasetFilter) {
      case 'only':
        groupStore.filter(["datasets", ">", 0]);
        break;
      case 'without':
        groupStore.filter([
          [ "fundingOrganization", ">", 0 ],
          "or",
          [ "fundingCycle", ">", 0 ],
          "or",
          [ ["researchGroup", ">", 0] , "and",  ["datasets", "=", 0] ],
      ]
    );
        break;
      default:
        groupStore.filter(null);
        break;
    }

    groupStore.load().done(function (data) {
      groups = data;
    });
    dsmTreeview.option("dataSource", groups);
    dsmTreeview.repaint();
    if (typeof selectedItem !== 'undefined') {
      loadGroupHtml(selectedItem);
    }
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
            dsmTreeview.unselectAll();
            dsmTreeview.collapseAll();
            dsmTreeview.repaint();
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

  const dsmTreeview = $('#simple-treeview').dxTreeView({
    dataStructure: 'plain',
    items: groups,
    parentIdExpr: 'parent',
    keyExpr: 'id',
    displayExpr: 'name',
    searchEnabled: true,
    searchExpr: ["name"],
    disabled: true,
    SearchEditorOptions: {
      placeholder: "Search",
      showClearButton: true,
      buttons: [
        "clear",
        {
          name: 'collapse',
          location: 'after',
          options: {
            icon: 'revert',
            stylingMode: 'contained',
            hint: 'Collape All',
            onClick() {
              dsmTreeview.option("searchValue", "");
              dsmTreeview.unselectAll();
              dsmTreeview.collapseAll();
              dsmTreeview.repaint();
            },
          },
        },
      ],
    },
    onInitialized() {
      loadGroups();
    },
    onItemClick(item) {
      selectedItem = item.itemData;
      loadGroupHtml(selectedItem);
    },
  }).dxTreeView('instance');

  const loadGroupHtml = (selectedItem) => {
    dsmLoadPanel.toggle(true);
    dsmTreeview.option("disabled", true);
    var parameters = {"datasetFilter": datasetFilter};
      if (selectedItem["fundingOrganization"] !== undefined) {
        parameters = Object.assign(parameters,{fundingOrganization : selectedItem.fundingOrganization});
      }
      if (selectedItem["fundingCycle"] !== undefined) {
        parameters = Object.assign(parameters,{fundingCycle : selectedItem.fundingCycle});
      }
      if (selectedItem["researchGroup"] !== undefined) {
        parameters = Object.assign(parameters,{researchGroup : selectedItem.researchGroup});
      }

      $.ajax({
        url: Routing.generate("app_api_dataset_monitoring_datasets",
          parameters
        ),
        method: "GET",
        accept: "text/html",
      }).then(function(html) {
        $("#datasets-here").html(html);
      }).done(function(){
        dsmLoadPanel.toggle(false);
        dsmTreeview.option("disabled", false);
      });
  }
});
