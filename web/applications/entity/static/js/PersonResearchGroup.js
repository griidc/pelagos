$(document).ready(function()
{
    "use strict";
    
    console.log('I loaded');

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
    $("form").on("reset", function() {
        var fundingOrganization = $(this).find("[fundingOrganization]");
        var fundingOrganizationValue = fundingOrganization.attr("fundingOrganization");
        fundingOrganization.find("option").attr("selected", false);
        fundingOrganization.val(fundingOrganizationValue);
        fundingOrganization.find("[value=\"" + fundingOrganizationValue + "\"]").attr("selected", true);
        fundingOrganization.change();

        var fundingCycle = $(this).find("[fundingCycle]");
        var fundingCycleValue = fundingCycle.attr("fundingCycle");
        fundingCycle.val(fundingCycleValue);
        fundingCycle.find("option[value=\"" + fundingCycleValue + "\"]").attr("selected", true);
        fundingCycle.change();
        
        var researchGroup = $(this).find("[researchGroup]");
        var researchGroupValue = researchGroup.attr("researchGroup");
        researchGroup.val(researchGroupValue);
        researchGroup.find("option[value=\"" + researchGroupValue + "\"]").attr("selected", true);
    });
});
