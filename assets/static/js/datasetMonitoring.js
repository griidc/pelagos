$(() => {
  var loadOnlyGroupsWithDatasets = false;
  var filterIcon = "filter";

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
      filterGroups(loadOnlyGroupsWithDatasets);
    }).always(function() {
      dsmTreeview.option("disabled", false);
    });
  }

  const filterGroups = (loadOnlyGroupsWithDatasets) =>
  {
    if (loadOnlyGroupsWithDatasets) {
      groupStore.filter(["datasets", ">", 0]);
    } else {
      groupStore.filter(null);
    }

    groupStore.load().done(function (data) {
      // console.log(data.length);
      groups = data;
    });
    dsmTreeview.option("dataSource", groups);
    dsmTreeview.repaint();
  }

  $('#dsm-filter').dxSwitch({
    value: loadOnlyGroupsWithDatasets,
    onValueChanged(e) {
      loadOnlyGroupsWithDatasets = e.value;
      filterGroups(loadOnlyGroupsWithDatasets);
    },
  });

  const dsmLoadPanel = $('.dsm-loadpanel').dxLoadPanel({
    // shadingColor: 'rgba(0,0,0,0.4)',
    // position: { of: '#dsm-side-picker' },
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
    searchEditorOptions: {
      placeholder: "Search",
      showClearButton: true,
      buttons: [
        "clear",
        {
          name: 'filter',
          location: 'after',
          options: {
            visible: false,
            icon: filterIcon,
            type: "normal",
            hint: "Show only groups that have datasets",
            onClick(e) {
              if (!loadOnlyGroupsWithDatasets) {
                loadOnlyGroupsWithDatasets = true;
              } else {
                loadOnlyGroupsWithDatasets = false;
              }

              filterGroups(loadOnlyGroupsWithDatasets);
            },
          },
        },
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
      dsmLoadPanel.toggle(true);
      dsmTreeview.option("disabled", true);
      const selectedItem = item.itemData;
      var parameters = {"loadOnlyGroupsWithDatasets": loadOnlyGroupsWithDatasets};
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
        // console.log('update');
      }).then(function (){
        // console.log('then');
      }).done(function(){
        dsmLoadPanel.toggle(false);
        dsmTreeview.option("disabled", false);
      });
    },
  }).dxTreeView('instance');
});
