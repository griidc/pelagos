$(document).ready(function()
{
    "use strict";

    var self = this;
    
    $(this).find("[name=\"fundingOrganization\"]").change(function () {
        var fundingCycle = $(self).find("[name=\"fundingCycle\"]");
        
        fundingCycle.removeAttr("disabled")
            .find("option").remove();
            
        if ($(this).val() === "") {
            fundingCycle.attr("disabled","disabled")
            .append("<option value=\"\">[Please Select a Funding Organization First]</option>");;
        } else {
            fundingCycle.append("<option value=\"\">[Please Select a Funding Cycle]</option>");
            addOptionsByEntity(
                fundingCycle,
                "FundingCycle", "fundingOrganization=" + $(this).val()
            );
        }
    });
    
    // Set FundingCycle list back to match with the original funding organization
    $(this).bind("reset", function() {
        var fundingOrganization = $(this).find("[name=\"fundingOrganization\"]");
        fundingOrganization.val(fundingOrganization.attr("fundingOrganization"))
            .change()
        var fundingCycle = $(self).find("[name=\"fundingCycle\"]");
        fundingCycle.val(fundingCycle.attr("fundingCycle"))
            .find("option[value=\"" + fundingCycle.val() + "\"]").attr("selected", true);
    });
});

/**
 * This function add funding org options to a select element
 *
 * @param selectElement element Element of Select item.
 * @param entity string Name of the entity.
 * @param filter string Filter for the entity.
 *
 * @return void
 */
function addOptionsByEntity(selectElement, entity, filter)
{
    "use strict";

    var url = pelagosBasePath + "/services/entity/" + entity;
    if (typeof filter !== "undefined") {
        url += "?" + filter + "&properties=id,name";
    } else {
        url += "?properties=id,name";
    }

    $.ajax({
        url: url,
        dataType: "json",
        async: false
    })
    .done(function(json) {
        var entities = sortObject(json.data, "name", false, true);

        $.each(entities, function(seq, item) {
            selectElement.append(
                $("<option></option>").val(item.id).html(item.name)
            );
        });
        //selectElement.find("option[value=\"" + selectElement.attr(entity) + "\"]").attr("selected", true);
    });
}
