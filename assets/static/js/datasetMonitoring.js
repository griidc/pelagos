$(() => {
  $.ajax({
    url: Routing.generate("app_api_dataset_monitoring_groups"),
    method: 'GET',
  }).then(function (response) {
    const groups = response;
    $('#simple-treeview').dxTreeView({
      dataStructure: 'plain',
      items: groups,
      parentIdExpr: 'parent',
      keyExpr: 'id',
      displayExpr: 'name',
      // showCheckBoxesMode: 'normal',
      // selectionMode: 'multiple',
      onItemClick(item) {
        const selectedItem = item.itemData;
        console.log(selectedItem);
      },
    });
  });
});
