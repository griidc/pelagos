$(document).ready(function()
{
    "use strict";
    populateFundingOrganizations($("#fundingOrganization"));

    $("#startDate").datepicker({
        dateFormat: "yy-mm-dd",
        onClose: function(selectedDate) {
            $("#endDate").datepicker("option", "minDate", selectedDate);
            var tomorrow = new Date(selectedDate);
            var newdate = tomorrow.setDate(tomorrow.getDate()+1);
            newdate = tomorrow.toISOString().substring(0, 10);
            $("#endDate").datepicker("option", "minDate", newdate);
        }
    });
    $("#endDate").datepicker({
        dateFormat: "yy-mm-dd",
        onClose: function(selectedDate) {
            var yesterday = new Date(selectedDate);
            var newdate = yesterday.setDate(yesterday.getDate()-1);
            newdate = yesterday.toISOString().substring(0, 10);
            $("#startDate").datepicker("option", "maxDate", newdate);
        }
    });
    
    $("form").has("#startDate,#endDate").bind("reset", function() {
        $("#startDate").datepicker("option", "maxDate", "");
        $("#endDate").datepicker("option", "minDate", "");
    });
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
