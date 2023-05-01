$(() => {
  $("#keywordList").on('keywordsAdded', function(event, { disabled }) {
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

          $('#add-button').dxButton({
            hint: "Add Keyword",
            icon: "add",
            text: "Add Keyword",
            stylingMode: 'contained',
            type: 'default',
            onClick() {
              const selectedRow = treeList.getSelectedRowsData();

              if (selectedRow.length > 0 && !selectedKeywords.includes(selectedRow[0])) {
                selectedKeywords.push(selectedRow[0]);
              }

              keywordList.reload();
              keywordList.repaint();

              var keywordItems = [];
              keywordList.option('items').forEach(function(item) {
                keywordItems.push(item.id);
              });

              // $('#keywords').val(keywordItems.join(','));

            },
          }).dxButton('instance');
        },
        searchPanel: { visible: true },
      }).dxTreeList('instance');

      const keywordList = $('#keywordList').dxList({
        dataSource: selectedKeywords,
        allowItemDeleting: true,
        itemDeleteMode: 'static',
        keyExpr: 'id',
        displayExpr: 'displayPath',
        noDataText: 'Please select some keywords',
        onItemDeleted(item) {
          console.log(item);
        }
      }).dxList('instance');
    });
  });
});
