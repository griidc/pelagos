$(() => {
  $.ajax({
    url: Routing.generate("app_api_dataset_monitoring_groups"),
    method: 'GET',
  }).then(function (response) {
    const groups = JSON.parse(response);
    $('#simple-treeview').dxTreeView({
      dataStructure: 'plain',
      items: groups,
    });
  });
});
