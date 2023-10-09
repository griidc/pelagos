$(() => {
    var selectedAnzsrcKeywords = [];
    var selectedGcmdKeywords = [];
    var anzsrcKeywords = [];
    var gcmdKeywords = [];
    const defaultAnzsrcTemplate = _.template($("#selecteditem").html());
    const defaultGcmdTemplate = _.template($("#selecteditem-gcmd").html());

    var xhr = new XMLHttpRequest();
    const url = Routing.generate("app_api_standard_keyword");

    DevExpress.ui.setTemplateEngine("underscore");

    xhr.open('GET', url + "?type=anzsrc", false);
    xhr.send(null);
    if (xhr.status === 200) {
      anzsrcKeywords = JSON.parse(xhr.responseText);
    }

    xhr.open('GET', url + "?type=gcmd", false);
    xhr.send(null);
    if (xhr.status === 200) {
      gcmdKeywords = JSON.parse(xhr.responseText);
    }

    const treeList = $('#treelist').dxTreeView({
      items: anzsrcKeywords,
      dataStructure: 'plain',
      rootValue: -1,
      keyExpr: 'referenceUri',
      parentIdExpr: 'parentUri',
      searchEnabled: true,
      displayExpr: 'label',
      disabled: false,
      searchEditorOptions: {
        onInput(e) {
          const value = e.component.option('value');
          if (value === '') {
            treeList.collapseAll();
          }
        },
      },
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
            if (!selectedAnzsrcKeywords.includes(selectedItem)) {
              selectedAnzsrcKeywords.push(selectedItem);

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

    const treeListGcmd = $('#treelist-gcmd').dxTreeView({
      items: gcmdKeywords,
      dataStructure: 'plain',
      rootValue: -1,
      keyExpr: 'referenceUri',
      parentIdExpr: 'parentUri',
      searchEnabled: true,
      displayExpr: 'label',
      disabled: false,
      searchEditorOptions: {
        onInput(e) {
          const value = e.component.option('value');
          if (value === '') {
            treeListGcmd.collapseAll();
          }
        },
      },
      onItemCollapsed(e) {
        if (e.itemIndex == 0) {
          treeListGcmd.expandItem('https://gcmd.earthdata.nasa.gov/kms/concept/e9f67a66-e9fc-435c-b720-ae32a2c3d8f5');
        }
      },
      onItemClick(item) {
        const selectedItem = item.itemData;
        var compiled = _.template($("#item-template-gcmd").html());

        if (item.node.expanded) {
          item.component.collapseItem(item.node.key);
        } else {
          item.component.expandItem(item.node.key);
        }

        $("#selecteditem-gcmd").html(compiled(selectedItem));

        $('#add-button-gcmd').dxButton({
          hint: "Add Keyword",
          icon: "add",
          text: "Add Keyword",
          stylingMode: 'contained',
          type: 'default',
          onClick() {
            if (!selectedGcmdKeywords.includes(selectedItem)) {
              selectedGcmdKeywords.push(selectedItem);

              var keywordListArray = [];
              const keyWordListValue = $("#keywordList").val();
              if (keyWordListValue !== "") {
                keywordListArray = keyWordListValue.split(',');
              }
              keywordListArray.push(selectedItem.id);

              $("#keywordList").val(keywordListArray.toString()).trigger("change");
            }
            keywordListGcmd.reload();
            keywordListGcmd.repaint();
          },
        });
      },
      searchPanel: { visible: true },
    }).dxTreeView('instance');

    const keywordList = $('#selectedList').dxList({
      dataSource: selectedAnzsrcKeywords,
      allowItemDeleting: false,
      itemDeleteMode: 'static',
      keyExpr: 'id',
      displayExpr: 'displayPath',
      noDataText: 'Please select at least one keyword.',
      itemTemplate: $('#list-item-template'),
      onItemRendered(item) {
        $("#keywordListAnzsrc").val(keywordList.getDataSource().items().map(item => {return item.id}).toString()).trigger("change");
        $(item.element).find('.dx-tag-remove-button').on('click', (e) => {
          keywordList.deleteItem(item.itemIndex);
        });
      },
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
        $("#keywordListAnzsrc").val(keywordList.getDataSource().items().map(item => {return item.id}).toString()).trigger("change");
      }
    }).dxList('instance');

    const keywordListGcmd = $('#selectedList-gcmd').dxList({
      dataSource: selectedGcmdKeywords,
      allowItemDeleting: false,
      itemDeleteMode: 'static',
      keyExpr: 'id',
      displayExpr: 'displayPath',
      noDataText: 'Please select at least one keyword.',
      itemTemplate: $('#list-item-template'),
      onItemRendered(item) {
        $("#keywordListGcmd").val(keywordListGcmd.getDataSource().items().map(item => {return item.id}).toString()).trigger("change");
        $(item.element).find('.dx-tag-remove-button').on('click', () => {
          keywordListGcmd.deleteItem(item.itemIndex);
        });
      },
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
        $("#keywordListGcmd").val(keywordListGcmd.getDataSource().items().map(item => {return item.id}).toString()).trigger("change");
      }
    }).dxList('instance');

    $("#keywordList").on('keywordsAdded', function(event, { disabled }) {
      $("#selecteditem").html(defaultAnzsrcTemplate);
      $("#selecteditem-gcmd").html(defaultGcmdTemplate);
      keywordList.option('disabled', disabled);
      keywordListGcmd.option('disabled', disabled);
      treeList.option('disabled', disabled);
      treeListGcmd.option('disabled', disabled);
      const value = $("#keywordList").val();

      keywordList.getDataSource().items().forEach(item => keywordList.deleteItem(0));
      anzsrcKeywords.filter(function(keyword) {
        if (value.split(",").includes(String(keyword.id))) {
          return keyword;
        }
      })
      .forEach(keyword => selectedAnzsrcKeywords.push(keyword));
      keywordList.reload();
      keywordList.repaint();

      keywordListGcmd.getDataSource().items().forEach(item => keywordList.deleteItem(0));
      gcmdKeywords.filter(function(keyword) {
        if (value.split(",").includes(String(keyword.id))) {
          return keyword;
        }
      })
      .forEach(keyword => selectedGcmdKeywords.push(keyword));
      keywordListGcmd.reload();
      keywordListGcmd.repaint();
    });
  });
