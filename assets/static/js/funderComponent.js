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
            var newElement = '<input id="funders_' + maxFunderId + '" name="funders[' + maxFunderId + ']" value="' + funderId + '">';
            $('[id^="funders_"]').last().after(newElement);
            maxFunderId++;
        }
    }

    function removeItemFromList(funderId) {
        $('[id^="funders_"]').each(function(id, element) {
            if ($(element).val() == funderId) {
                console.log('found element');
                //remove element
                $(element).remove();
                // haveDuplicate = true;
            }
        });
        // var index = arr.indexOf(value);
        // if (index > -1) {
        //   arr.splice(index, 1);
        // }
        // return arr;
    }

    const addedFunders = [];

    $('[id^="funders_"]').each(function(id, element) {
        addedFunders.push($(element).val());
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
            event.removedItems.forEach(removedItem => {
                removeItemFromList(removedItem.id);
            });
            event.addedItems.forEach(addedItem => {
                addFunderToList(addedItem.id);
            });

            // Add funders for funder collection.
            // $("#funder").val(selectedFunders);
        },
    });
});
