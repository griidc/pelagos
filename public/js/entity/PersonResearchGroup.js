$(document).ready(function()
{
    "use strict";

    // Set FundingCycle list back to match with the original funding organization
    $("form[entityType=\"PersonResearchGroup\"]").on("reset", function(event) {
        var form = event.target;

        var fundingOrganization = $("#fundingOrganization", form);
        var fundingOrganizationValue = fundingOrganization.attr("fundingOrganization");
        fundingOrganization.find("option").attr("selected", false);
        fundingOrganization.val(fundingOrganizationValue);
        fundingOrganization.find("[value=\"" + fundingOrganizationValue + "\"]").attr("selected", true);
        fundingOrganization.change();

        var fundingCycle = $("#fundingCycle", form);
        var fundingCycleValue = fundingCycle.attr("fundingCycle");
        fundingCycle.val(fundingCycleValue);
        fundingCycle.find("option[value=\"" + fundingCycleValue + "\"]").attr("selected", true);
        fundingCycle.change();

        var researchGroup = $("#researchGroup", form);
        var researchGroupValue = researchGroup.attr("researchGroup");
        researchGroup.val(researchGroupValue);
        researchGroup.find("option[value=\"" + researchGroupValue + "\"]").attr("selected", true);

        $("#person", form).select2("val", $("#person").attr("person"));
    });
});
