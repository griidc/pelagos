$(document).ready(function()
{
    "use strict";

    var self = this;

    addOptionsByEntity($(this).find("[name=\"fundingOrganization\"]"), "FundingOrganization");

    $(this).find("[name=\"fundingOrganization\"]").change(function () {
        $(self).find("[name=\"fundingCycle\"]").find("option").remove();
        $(self).find("[name=\"fundingCycle\"]").removeAttr("disabled")
        .append("<option value=\"\">[Please Select a Funding Cycle]</option>");

        addOptionsByEntity(
            $(self).find("[name=\"fundingCycle\"]"),
            "FundingCycle", "fundingOrganization=" + $(this).val()
        );
        
        if ($(this).val() === "") {
            $(self).find("[name=\"fundingCycle\"]").attr("disabled","disabled");
        } else {
            
        }
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

    $.getJSON(url, function(json) {
        var entities = sortObject(json.data, "name", false, true);

        $.each(entities, function(seq, item) {
            selectElement.append(
                $("<option></option>").val(item.id).html(item.name)
            );
        });
        selectElement.find("option[value=\"" + selectElement.attr(entity) + "\"]").attr("selected", true);
    });
}
