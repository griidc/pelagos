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
              // selectedKeywords.push(selectedItem);
              var tempArray = $("#keywordList").val().split(',');

              const index = tempArray.indexOf(item.id);
              if (index > -1) { // only splice array when item is found
                tempArray.splice(index, 1); // 2nd parameter means remove one item only
              }
              $("#keywordList").val(tempArray.toString()).trigger("change");
              // $("#keywordList").val(selectedKeywords.map(keyword => keyword["id"]).toString()).trigger("change");
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
        console.log('delete');
        console.log(item.itemData);
        var tempArray = $("#keywordList").val().split(',');

        const index = tempArray.indexOf(String(item.itemData.id));
        if (index > -1) { // only splice array when item is found
          tempArray.splice(index, 1); // 2nd parameter means remove one item only
        }
        console.log(tempArray);
        $("#keywordList").val(tempArray.toString()).trigger('change');
      }
    }).dxList('instance');

    $("#keywordList").on('keywordsAdded', function() {
      console.log('list');
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
