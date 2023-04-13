$(() => {
  $.ajax({
    url: '/api/standard/keyword?type=anzsrc',
    dataType: 'json',
  }).then((result) => {
    $('#treelist').dxTreeList({
      dataSource: result,
      rootValue: -1,
      keyExpr: 'key',
      parentIdExpr: 'parent',
      columns: [{
          dataField: 'label',
          caption: 'Keywords',
        }],
      expandedRowKeys: [1],
      showRowLines: true,
      showBorders: true,
      columnAutoWidth: true,
      sorting: {
          mode: "none",
      },
      searchPanel: { visible: true },
    });
  });
});
