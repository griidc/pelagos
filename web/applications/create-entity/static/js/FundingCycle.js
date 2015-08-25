$(document).ready(function()
{
    "use strict";
    populateFundingOrganisations($("#fundingOrganisation"));
});

function populateFundingOrganisations(selectElement)
{
    "use strict";
    var url = pelagosBasePath + "/services/entity/fundingOrganization/";

    $.getJSON(url, function(data) {
        var fundingOrganizations = sortResults(data.data.FundingOrganizations, "name", false, true);
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
