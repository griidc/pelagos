$(() => {
  var selectedKeywords = [];
  var allKeywords = [];

  $.ajax({
    url: Routing.generate("app_api_standard_keyword") + "?type=anzsrc",
    dataType: 'json',
  }).then((result) => {
    allKeywords = result;
    $('#treelist').dxTreeView({
      items: allKeywords,
      dataStructure: 'plain',
      rootValue: -1,
      keyExpr: 'referenceUri',
      parentIdExpr: 'parentUri',
      searchEnabled: true,
      displayExpr: 'label',
      onItemClick(item) {
        const selectedItem = item.itemData;
        var compiled = _.template($("#item-template ").html());

        $("#selecteditem").html(compiled(selectedItem));

        $('#add-button').dxButton({
          hint: "Add Keyword",
          icon: "add",
          text: "Add Keyword",
          stylingMode: 'contained',
          type: 'default',
          onClick() {
            if (!selectedKeywords.includes(selectedItem)) {
              selectedKeywords.push(selectedItem);

              var keywordListArray = [];
              const keyWordListValue = $("#keywordList").val();
              if (keyWordListValue !== "") {
                keywordListArray = keyWordListValue.split(',');
              }
              keywordListArray.push(selectedItem.id);

              $("#keywordList").val(keywordListArray.toString()).trigger("change");
            }
            keywordList.reload();
            keywordList.repaint();
          },
        });
      },
      searchPanel: { visible: true },
    });

    const keywordList = $('#selectedList').dxList({
      dataSource: selectedKeywords,
      allowItemDeleting: true,
      itemDeleteMode: 'static',
      keyExpr: 'id',
      displayExpr: 'displayPath',
      noDataText: 'Please select some keywords',
      onItemDeleted(item) {
        var keywordListArray = [];
        const keyWordListValue = $("#keywordList").val();
        if (keyWordListValue !== "") {
          keywordListArray = keyWordListValue.split(',');
        }

        const index = keywordListArray.indexOf(String(item.itemData.id));
        if (index > -1) {
          keywordListArray.splice(index, 1);
        }
        $("#keywordList").val(keywordListArray.toString()).trigger('change');
      }
    }).dxList('instance');

    $("#keywordList").on('keywordsAdded', function() {
      var value = $("#keywordList").val();
      keywordList.getDataSource().items().forEach(item => keywordList.deleteItem(0));
      allKeywords.filter(function(keyword) {
        if (value.split(",").includes(String(keyword.id))) {
          return keyword;
        }
      })
      .forEach(keyword => selectedKeywords.push(keyword));
      keywordList.reload();
      keywordList.repaint();
    });
  });
});
