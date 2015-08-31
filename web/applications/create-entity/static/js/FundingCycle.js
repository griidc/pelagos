$(document).ready(function()
{
    "use strict";
    populateFundingOrganizations($("#fundingOrganization"));

    $("#startDate").datepicker({
        dateFormat: "yy-mm-dd",
        onClose: function(selectedDate) {
            $("#endDate").datepicker("option", "minDate", selectedDate);
        }
    });
    $("#endDate").datepicker({
        dateFormat: "yy-mm-dd",
        onClose: function(selectedDate) {
            $("#startDate").datepicker("option", "maxDate", selectedDate);
        }
    });
    
    // $("#startDate").rules("add", {
        // required: function(element) {
            // return $("#endDate").val() != "";
        // }
    // });
    
    // $("#endDate").rules("add", {
        // required: function(element) {
            // return $("#startDate").val() != "";
        // }
    // });
    
   
});


/**
 * This function add funding org options to a select element
 *
 * @param selectElement element Element of Select item
 *
 * @return void
 */
function populateFundingOrganizations(selectElement)
{
    "use strict";
    var url = pelagosBasePath + "/services/entity/FundingOrganization";

    $.getJSON(url, function(json) {
        var fundingOrganizations = sortResults(json.data, "name", false, true);

        $.each(fundingOrganizations, function(seq, FundingOrg) {
            selectElement.append(
            $("<option></option>").val(FundingOrg.id).html(FundingOrg.name)
            );
        });
    });
}

/**
 * This function will sort an object.
 *
 * @param data jsonData The data to be sorted.
 * @param propery string Sort by property name.
 * @param desc boolean Flag on order, true is descending
 * @param ignorecase boolean Flag to ignore case.
 *
 * @return object
 */
function sortResults(data, property, desc, ignorecase) {
    "use strict";
    return data.sort(function(a, b) {
        if (!ignorecase) {
            if (!desc) {
                return (a[property] > b[property]) ? 1 : ((a[property] < b[property]) ? -1 : 0);
            } else {
                return (b[property] > a[property]) ? 1 : ((b[property] < a[property]) ? -1 : 0);
            }
        } else {
            if (!desc) {
                return (a[property].toLowerCase() > b[property].toLowerCase())
                ? 1 : ((a[property].toLowerCase() < b[property].toLowerCase()) ? -1 : 0);
            } else {
                return (b[property].toLowerCase() > a[property].toLowerCase())
                ? 1 : ((b[property] < a[property].toLowerCase()) ? -1 : 0);
            }
        }

    });
}
