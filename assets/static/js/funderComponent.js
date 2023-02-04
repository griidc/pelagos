$(function() {
    "use strict";

    var maxFunderId = 0;
    var addedFunders = [];

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
            newElement.type = "hidden";
            $('[id="funder-items"]').append(newElement);
            maxFunderId++;
            addedFunders.push(funderId);
            $("#funderList").val(addedFunders.toString()).trigger("change");
        }
    }

    function removeItemFromList(funderId) {
        $('[id^="funders_"]').each(function(id, element) {
            if ($(element).val() == funderId) {
                $(element).remove();
            }
            let index = addedFunders.indexOf(funderId);
            if (index > -1) {
                addedFunders.splice(index, 1);
            }
            $("#funderList").val(addedFunders.toString()).trigger("change");
        });
    }

    $('[id^="funders_"]').each(function(id, element) {
        var value = $(element).val();
        addedFunders.push(parseInt(value));
        maxFunderId++;
    });

    $("#funderList").val(addedFunders.toString());
    $("#funderList").on("change", function(event){
        console.log(event);
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
        inputAttr: { id: 'devExtremeID' },
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
