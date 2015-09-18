$.ajax({
    url: pelagosComponentPath + "/static/js/ResearchGroup.js",
    dataType: "script",
    cache: true
});

$(document).ready(function()
{
    "use strict";
    $(".entityForm[entityType=\"ResearchGroup\"] [name=\"logo\"]").on("logoChanged", function ()
    {
        if ($(this).attr("mimeType") !== "application/x-empty") {
            $("#researchGroupLogo").html("<img src=\"data:" + $(this).attr("mimeType") + ";base64," + $(this).attr("base64") + "\">");
        }
    });
});
