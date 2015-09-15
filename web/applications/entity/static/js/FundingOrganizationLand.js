$.ajax({
    url: pelagosComponentPath + "/static/js/FundingCycle.js",
    dataType: "script",
    cache: true
});

$(document).ready(function()
{
    "use strict";
    $(".entityForm[entityType=\"FundingOrganization\"] [name=\"logo\"]").on("logoChanged", function ()
    {
        if ($(this).attr("mimeType") !== "application/x-empty") {
            $("#fundingOrganizationLogo").html("<img src=\"data:" + $(this).attr("mimeType") + ";base64," + $(this).attr("base64") + "\">");
        }
    });
});
