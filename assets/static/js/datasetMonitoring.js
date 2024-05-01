$(() => {
  $.ajax({
    url: Routing.generate("app_api_dataset_monitoring_groups"),
    method: 'GET',
    dataType : 'json',
  }).then(function (response) {
    const groups = response;
    $('#simple-treeview').dxTreeView({
      dataStructure: 'plain',
      items: groups,
      parentIdExpr: 'parent',
      keyExpr: 'id',
      displayExpr: 'name',
      searchEnabled: true,
      onItemClick(item) {
        const selectedItem = item.itemData;
        var parameters = {};
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
        });
      },
    });
  });
});
