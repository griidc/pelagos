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

$(document).ready(function()
{
    "use strict";

    $("#person").select2({
        placeholder: "[Please Select a Person]",
        allowClear: true
    });

    $("[fundingOrganization]").change(function () {
        var fundingCycle = $(this).nextAll("[fundingCycle]");
        fundingCycle.removeAttr("disabled")
            .find("option").remove();

        if ($(this).val() === "") {
            fundingCycle.attr("disabled", "disabled")
            .append("<option value=\"\">[Please Select a Funding Organization First]</option>");
        } else {
            fundingCycle.append("<option value=\"\">[Please Select a Funding Cycle]</option>");
            addOptionsByEntity(
                fundingCycle,
                "FundingCycle", "fundingOrganization=" + $(this).val()
            );
        }
        $("[fundingCycle]").change();
    });

    $("[fundingCycle]").change(function () {
        var researchGroup = $(this).nextAll("[researchGroup]");
        researchGroup.removeAttr("disabled")
        .find("option").remove();

        if ($(this).val() === "") {
            researchGroup.attr("disabled", "disabled")
            .append("<option value=\"\">[Please Select a Funding Cycle First]</option>");
        } else {
            researchGroup.append("<option value=\"\">[Please Select a Research Group]</option>");
            addOptionsByEntity(
            researchGroup,
            "ResearchGroup", "fundingCycle=" + $(this).val()
            );
        }
    });

    // Set FundingCycle list back to match with the original funding organization
    $("form[entityType=\"PersonResearchGroup\"]").on("reset", function(event) {
        var form = event.target;

        var fundingOrganization = $("[fundingOrganization]", form);
        var fundingOrganizationValue = fundingOrganization.attr("fundingOrganization");
        fundingOrganization.find("option").attr("selected", false);
        fundingOrganization.val(fundingOrganizationValue);
        fundingOrganization.find("[value=\"" + fundingOrganizationValue + "\"]").attr("selected", true);
        fundingOrganization.change();

        var fundingCycle = $("[fundingCycle]", form);
        var fundingCycleValue = fundingCycle.attr("fundingCycle");
        fundingCycle.val(fundingCycleValue);
        fundingCycle.find("option[value=\"" + fundingCycleValue + "\"]").attr("selected", true);
        fundingCycle.change();

        var researchGroup = $("[researchGroup]", form);
        var researchGroupValue = researchGroup.attr("researchGroup");
        researchGroup.val(researchGroupValue);
        researchGroup.find("option[value=\"" + researchGroupValue + "\"]").attr("selected", true);

        $("[person]", form).select2("val", $("[person]").attr("person"));
    });
});
