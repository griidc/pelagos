$(() => {

  var selectedKeywords = [];
  var maxKeywordId = 0;
  var addedKeywords = [];
  var allKeywords = [];

  function addKeywordToList(keywordId) {
    var haveDuplicate = false;
    $('[id^="keywords_"]').each(function(id, element) {
        if ($(element).val() == keywordId) {
            haveDuplicate = true;
        }
    });
    if (!haveDuplicate) {
        var newElement = document.createElement("input");
        newElement.id = `keywords_${maxKeywordId}`;
        newElement.name = `keywords[${maxKeywordId}]`;
        newElement.value = keywordId;
        newElement.type = "hidden";
        $('[id="keyword-items"]').append(newElement);
        maxKeywordId++;
        addedKeywords.push(keywordId);
        $("#keywordList").val(addedKeywords.toString()).trigger("change");
    }
  }

  function removeKeywordFromList(keywordId) {
      $('[id^="keywords_"]').each(function(id, element) {
          if ($(element).val() == keywordId) {
              $(element).remove();
          }
          let index = addedKeywords.indexOf(keywordId);
          if (index > -1) {
            addedKeywords.splice(index, 1);
          }
          $("#keywordList").val(addedKeywords.toString()).trigger("change");
      });
  }

  $.ajax({
    url: Routing.generate("app_api_standard_keyword") + "?type=gcmd",
    dataType: 'json',
  }).then((result) => {
    allKeywords = result;
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
              var keywordItem = selectedRow[0];
              console.log(keywordItem);
              selectedKeywords.push(keywordItem);
              addKeywordToList(keywordItem.id);
            }

            keywordList.reload();
            keywordList.repaint();

            var keywordItems = [];
            keywordList.option('items').forEach(function(item) {
              keywordItems.push(item.id);
            });
          },
        }).dxButton('instance');
      },
      searchPanel: { visible: true },
    }).dxTreeList('instance');

    const keywordList = $('#selectedList').dxList({
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


    $("#keywordList").on('keywordsAdded', function(event, { disabled }) {
      $('[id^="keywords_"]').each(function(id, element) {
          var value = $(element).val();
          addedKeywords.push(parseInt(value));
          maxKeywordId++;
          // console.log(allKeywords);
          console.log(value);
          var keywordItem = allKeywords.find((keyword, index) => {
            return keyword.id = value;
          });
          // var keywordItem = allKeywords.find(function(keyword) {
          //   console.log(value);
          //   return keyword.id = 16;
          // });
          console.log(keywordItem);
          selectedKeywords.push(keywordItem);

          keywordList.reload();
          keywordList.repaint();
      });

      $("#keywordList").val(addedKeywords.toString());
    });
  });
});
