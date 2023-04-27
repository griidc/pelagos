$(() => {
  var selectedKeywords = [];

  $.ajax({
    url: Routing.generate("app_api_standard_keyword") + "?type=gcmd",
    dataType: 'json',
  }).then((result) => {
    const treeList = $('#treelist').dxTreeList({
      dataSource: result,
      rootValue: -1,
      keyExpr: 'referenceUri',
      parentIdExpr: 'parentUri',
      columns: [{
        dataField: 'label',
        caption: 'Keywords',
      }],
      expandedRowKeys: [1],
      showRowLines: true,
      showBorders: true,
      columnAutoWidth: true,
      showColumnHeaders: false,
      selection: {
        mode: 'single',
      },
      onSelectionChanged() {
        const selectedData = treeList.getSelectedRowsData();
    
        const selectedItem = selectedData[0];
 
        var compiled = _.template($("#item-template ").html());

        $("#selecteditem").html(compiled(selectedItem));
      },
      searchPanel: { visible: true },
    }).dxTreeList('instance');

    const listWidget = $('#simpleList').dxList({
      dataSource: selectedKeywords,
      allowItemDeleting: true,
      itemDeleteMode: 'static',
      displayExpr: 'displayPath',
      noDataText: 'Please select some keywords',
    }).dxList('instance');

    $('#add-button').dxButton({
      text: 'Add',
      icon: 'add',
      width: 120,
      onClick() {
        const selectedRow = treeList.getSelectedRowsData();
        
        if (selectedRow.length > 0 && !selectedKeywords.includes(selectedRow[0])) {
          selectedKeywords.push(selectedRow[0]);
        }

        listWidget.reload();
        listWidget.repaint();
      },
    });
  });
});
