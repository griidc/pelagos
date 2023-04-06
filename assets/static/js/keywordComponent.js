$(() => {

    stuff =  $.ajax({
        url: '/api/standard/keyword',
        dataType: 'json',
      }).then((result) => {
        console.log(result);
        $('#treelist').dxTreeList({
            dataSource: result,
            rootValue: -1,
            keyExpr: 'notation',
            parentIdExpr: 'parentId',
            columns: [{
                dataField: 'label',
                caption: 'Keyword',
              }],
            expandedRowKeys: [1],
            showRowLines: true,
            showBorders: true,
            columnAutoWidth: true,
            sorting: {
                mode: "none",
            },
            searchPanel: { visible: true },
            selection: {
                mode: 'multiple',
                recursive: false,
              },
          });
      });

    // $('#treelist').dxTreeList({
    //   dataSource: {
    //     load(options) {
    //       return $.ajax({
    //         url: 'https://localhost:8082/api/standard/keyword',
    //         dataType: 'json',
    //         data: { parentIds: options.parentIds },
    //       }).then((result) => ({
    //         data: result,
    //       }));
    //     },
    //   },
    //   scrolling: {
    //     mode: 'standard',
    //   },
    //   showColumnHeaders: false,
    //   allowSearch: true,
    //   remoteOperations: {
    //     filtering: true,
    //   },
    //   keyExpr: 'notation',
    //   parentIdExpr: 'parentId',
    //   hasItemsExpr: 'hasItems',
    //   rootValue: '',
    //   showBorders: true,
    //   columns: [
    //     { dataField: 'label' },
    //   ],
    // });
  });
