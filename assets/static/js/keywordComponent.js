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
              addKeywordToList(selectedItem.id);
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
        removeKeywordFromList(item.id);
      }
    }).dxList('instance');

    $("#keywordList").on('keywordsAdded', function(event, { disabled }) {
      keywordList.getDataSource().items().forEach(item => keywordList.deleteItem(0));

      var value = $("#keywordList").val();
      allKeywords.filter(function(keyword) {
        if (!value) {
          return;
        }
        return String(keyword.id).match(value.replace(/\s?\,\s?/g, "|"));
      })
      .forEach(keyword => selectedKeywords.push(keyword));

      keywordList.reload();
      keywordList.repaint();
    });
  });
});
