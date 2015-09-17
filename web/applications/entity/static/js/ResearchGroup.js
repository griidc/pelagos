$(document).ready(function()
{
    "use strict";
    
    var self = this;
    
    debugger;
    
    addOptionsByEntity($(this).find("[name=\"fundingOrganization\"]"), "FundingOrganization");
    
    $(this).find("[name=\"fundingOrganization\"]").change(function () {
        addOptionsByEntity($(self).find("[name=\"fundingCycle\"]"), "FundingCycle");
    });
});

/**
 * This function add funding org options to a select element
 *
 * @param selectElement element Element of Select item
 *
 * @return void
 */
function addOptionsByEntity(selectElement, Entity)
{
    "use strict";
    var url = pelagosBasePath + "/services/entity/" + Entity + "?properties=id,name";

    $.getJSON(url, function(json) {
        var entities = sortObject(json.data, "name", false, true);

        $.each(entities, function(seq, item) {
            selectElement.append(
                $("<option></option>").val(item.id).html(item.name)
            );
        });
        selectElement.find("option[value=\"" + selectElement.attr(Entity) + "\"]").attr("selected", true);
    });
}