$(function() {
    "use strict";

    const selectedFunders = [];

    function removeItemFromList(arr, value) {
        var index = arr.indexOf(value);
        if (index > -1) {
          arr.splice(index, 1);
        }
        return arr;
    }

    const addedFunders = [];

    $.ajax({
        url: Routing.generate('pelagos_api_datasets_get_collection') + '?udi=' + $("#regForm").attr("udi") + '&_properties=funders.id',
        success: function(data){
            if (data && data.length > 0) {
                let associatedFunders = data[0].funders;
                associatedFunders.forEach((funder) => {
                  addedFunders.push(funder.id);
                });
            }
        },
    }).always(function() {
        $("#funderTagBox").dxTagBox({
            placeholder: 'Choose Funder...',
            width: "90%",
            dataSource: Routing.generate('app_api_funders_by_name'),
            displayExpr: 'name',
            value: addedFunders,
            valueExpr: 'id',
            searchEnabled: true,
            acceptCustomValue: true,
            onSelectionChanged(event) {
                event.addedItems.forEach(addedItem => {
                    selectedFunders.push(addedItem.id);
                });
                event.removedItems.forEach(removedItem => {
                    removeItemFromList(selectedFunders, removedItem.id);
                });
                $("#funders").val(selectedFunders);
            },
            onCustomItemCreating(args) {
                const newItem = {
                    id: -1,
                    name: args.text.trim(),
                }
                const { component } = args;
                const currentItems = component.option('items');
                currentItems.unshift(newItem);
                component.option('items', currentItems);
                args.customItem = newItem;
                selectedFunders.push(newItem.name);
            },
        });
    });
});
