$(() => {
    $('#gridContainer').dxDataGrid({
        dataSource: `${Routing.generate('pelagos_app_api_keyword_dataset')}`,
        columns: [
            {
                type: 'buttons',
                width: 110,
                buttons: [{
                    hint: 'Edit',
                    icon: 'edit',
                    onClick(e) {
                        const udi = e.row.data.udi;
                        e.event.preventDefault();
                        window.open(`${Routing.generate('pelagos_app_ui_edit_keyword_dataset')}/${udi}`);
                    },
                }],
            },
            {
                dataField: 'udi',
                caption: 'UDI',
                width: 200,
            },
            {
                dataField: 'title',
                caption: 'Title',
            },
            {
                dataField: 'acceptedDate',
                caption: 'Accepted Date',
                dataType: 'date',
                width: 250,
            },
            {
                dataField: 'keywords',
                caption: 'Number of Keywords',
                width: 150,
                filterValue: 0,
            },
        ],
        showBorders: true,
        showColumnLines: true,
        showRowLines: true,
        rowAlternationEnabled: true,
        filterPanel: {
           visible: true,
        },
        filterRow: {
            visible: true,
        },
        summary: {
            totalItems: [{
                column: 'udi',
                summaryType: 'count',
            }],
        },
    });
});
