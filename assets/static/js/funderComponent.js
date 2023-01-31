$(function() {
    "use strict";

    var maxFunderId = 0;

    function addFunderToList(funderId) {
        var haveDuplicate = false;
        $('[id^="funders_"]').each(function(id, element) {
            if ($(element).val() == funderId) {
                haveDuplicate = true;
            }
        });
        if (!haveDuplicate) {
            var newElement = document.createElement("input");
            newElement.id = `funders_${maxFunderId}`;
            newElement.name = `funders[${maxFunderId}]`;
            newElement.value = funderId;
            $('[id="funder-items"]').append(newElement);
            maxFunderId++;
        }
    }

    function removeItemFromList(funderId) {
        $('[id^="funders_"]').each(function(id, element) {
            if ($(element).val() == funderId) {
                $(element).remove();
            }
        });
    }

    var addedFunders = [];

    $('[id^="funders_"]').each(function(id, element) {
        var value = $(element).val();
        addedFunders.push(parseInt(value));
        maxFunderId++;
    });

    $("#funderTagBox").dxTagBox({
        placeholder: 'Choose Funder...',
        width: "90%",
        dataSource: Routing.generate('app_api_funders_by_name'),
        displayExpr: 'name',
        value: addedFunders,
        valueExpr: 'id',
        searchEnabled: true,
        acceptCustomValue: false,
        onSelectionChanged(event) {
            event.addedItems.forEach(addedItem => {
                addFunderToList(addedItem.id);
            });
            event.removedItems.forEach(removedItem => {
                removeItemFromList(removedItem.id);
            });
        },
    });
});
