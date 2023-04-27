$(() => {
  var selectedKeywords = [];

  $.ajax({
    url: '/api/standard/keyword?type=gcmd',
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
        // TODO: Show selected show in display somewhere
      },
      searchPanel: { visible: true },
    }).dxTreeList('instance');

    const listWidget = $('#simpleList').dxList({
      dataSource: selectedKeywords,
      allowItemDeleting: true,
      itemDeleteMode: 'static',
      displayExpr: 'displayPath',
    }).dxList('instance');

    $('#add-button').dxButton({
      text: 'Add',
      icon: 'add',
      width: 120,
      onClick() {
        const selectedRow = treeList.getSelectedRowsData();
        console.log(selectedRow);
        
        if (selectedRow.length > 0 && !selectedKeywords.includes(selectedRow[0])) {
          selectedKeywords.push(selectedRow[0]);
        }

        listWidget.reload();
        listWidget.repaint();
      },
    });
  });
});
