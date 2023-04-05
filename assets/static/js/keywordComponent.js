$(() => {
    $('#treelist').dxTreeList({
      dataSource: {
        load(options) {
          return $.ajax({
            url: 'https://localhost:8082/api/standard/keyword',
            dataType: 'json',
            data: { parentIds: options.parentIds },
          }).then((result) => ({
            data: result,
          }));
        },
      },
      scrolling: {
        mode: 'standard',
      },
      showColumnHeaders: false,
      allowSearch: true,
      remoteOperations: {
        filtering: true,
      },
      keyExpr: 'notation',
      parentIdExpr: 'parentId',
      hasItemsExpr: 'hasItems',
      rootValue: '',
      showBorders: true,
      columns: [
        { dataField: 'label' },
      ],
    });
  });