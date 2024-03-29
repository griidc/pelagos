$(() => {
  var selectedKeywords = [];
  var allKeywords = [];
  const defaultTemplate = _.template($("#selecteditem").html());

  var xhr = new XMLHttpRequest();
  const url = Routing.generate("app_api_standard_keyword") + "?type=anzsrc";
  xhr.open('GET', url, false);
  xhr.send(null);

  if (xhr.status === 200) {
    allKeywords = JSON.parse(xhr.responseText);
  }

  const treeList = $('#treelist').dxTreeView({
    items: allKeywords,
    dataStructure: 'plain',
    rootValue: -1,
    keyExpr: 'referenceUri',
    parentIdExpr: 'parentUri',
    searchEnabled: true,
    displayExpr: 'label',
    disabled: false,
    onItemClick(item) {
      const selectedItem = item.itemData;
      var compiled = _.template($("#item-template").html());

      if (item.node.expanded) {
        item.component.collapseItem(item.node.key);
      } else {
        item.component.expandItem(item.node.key);
      }

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
  }).dxTreeView('instance');

  const keywordList = $('#selectedList').dxList({
    dataSource: selectedKeywords,
    allowItemDeleting: true,
    itemDeleteMode: 'static',
    keyExpr: 'id',
    displayExpr: 'displayPath',
    noDataText: 'Please select at least one keyword.',
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

  $("#keywordList").on('keywordsAdded', function(event, { disabled }) {
    $("#selecteditem").html(defaultTemplate);
    keywordList.option('allowItemDeleting', !disabled);
    treeList.option('searchValue', '');
    treeList.option('disabled', disabled);
    treeList.collapseAll();
    const value = $("#keywordList").val();
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
