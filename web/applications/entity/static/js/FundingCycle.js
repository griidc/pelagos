$(document).ready(function()
{
    "use strict";
    populateFundingOrganizations($(".entityForm[entityType=\"FundingCycle\"] [name=\"fundingOrganization\"]"));

    var startDateField = $(".entityForm[entityType=\"FundingCycle\"] [name=\"startDate\"]");
    var endDateField = $(".entityForm[entityType=\"FundingCycle\"] [name=\"endDate\"]");

    startDateField.datepicker({
        dateFormat: "yy-mm-dd",
        onClose: function(selectedDate) {
            endDateField.datepicker("option", "minDate", selectedDate);
            try {
                var tomorrow = new Date(selectedDate);
                var newdate = tomorrow.setDate(tomorrow.getDate() + 1);
                newdate = tomorrow.toISOString().substring(0, 10);
                endDateField.datepicker("option", "minDate", newdate);
            }
            catch (e) {
                /* do nothing
                 * catches if a bad date is entered
                 * */
            }

            startDateField.keyup();
        }
    });

    endDateField.datepicker({
        dateFormat: "yy-mm-dd",
        onClose: function(selectedDate) {
            try {
                var yesterday = new Date(selectedDate);
                var newdate = yesterday.setDate(yesterday.getDate() - 1);
                newdate = yesterday.toISOString().substring(0, 10);
                startDateField.datepicker("option", "maxDate", newdate);
            }
            catch (e) {
                /* do nothing
                 * catches if a bad date is entered
                 * */
            }
            endDateField.keyup();
        }
    });

    $(".entityForm[entityType=\"FundingCycle\"]").bind("reset", function() {
        startDateField.datepicker("option", "maxDate", "");
        endDateField.datepicker("option", "minDate", "");
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
        var fundingOrganizations = sortObject(json.data, "name", false, true);

        $.each(fundingOrganizations, function(seq, FundingOrg) {
            selectElement.append(
                $("<option></option>").val(FundingOrg.id).html(FundingOrg.name)
            );
        });
        selectElement.find("option[value=\"" + selectElement.attr("fundingOrganization") + "\"]").attr("selected", true);
    });
}
