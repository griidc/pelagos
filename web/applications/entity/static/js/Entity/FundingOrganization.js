$(document).ready(function()
{
    "use strict";

    $(".entityForm[entityType=\"FundingOrganization\"]").each(function () {
        populateDataRepositories($(this).find("[name=\"dataRepository\"]"));
    });
});

/**
 * This function add funding org options to a select element
 *
 * @param selectElement element Element of Select item
 *
 * @return void
 */
function populateDataRepositories(selectElement)
{
    "use strict";
    var url = pelagosBasePath + "/services/entity/DataRepository";

    $.getJSON(url, function(json) {
        var dataRepositories = sortObject(json.data, "name", false, true);

        $.each(dataRepositories, function(seq, dataRepo) {
            selectElement.append(
                $("<option></option>").val(dataRepo.id).html(dataRepo.name)
            );
        });
        selectElement.find("option[value=\"" + selectElement.attr("dataRepository") + "\"]").attr("selected", true);
    });
}
