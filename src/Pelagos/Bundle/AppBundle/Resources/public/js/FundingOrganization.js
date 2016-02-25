var $ = jQuery.noConflict();

$(document).ready(function()
{
    "use strict";
    $(".entityForm[entityType=\"FundingOrganization\"] [name=\"logo\"]").on("logoChanged", function ()
    {
        if ($(this).attr("mimeType") !== "application/x-empty") {
            $("#fundingOrganizationLogo").html("<img src=\"data:" + $(this).attr("mimeType") + ";base64," + $(this).attr("base64") + "\">");
        }
    });

    $(".entityForm[entityType=\"PersonFundingOrganization\"]").on("entityDelete", function (event, deleteId)
    {
        $("#leadership tr[personfundingorganizationid=\"" + deleteId + "\"]")
        .animate({ height: "toggle", opacity: "toggle" }, "slow", function() {
            $(this).slideUp("fast", function() {
                $(this)
                .remove();
            });
        });

    });

    $("#logobutton")
        .button()
        .click(function() {
              $("#fileupload").click();
        });

    $("#fileupload").fileupload({
        url: $(this).attr("data-url"),
        method: "POST",
        done: function (e, data) {
            $("#fundingOrganizationLogo img").attr("src", data.url);
        }
    }).prop("disabled", !$.support.fileInput)
    .parent().addClass($.support.fileInput ? undefined : "disabled");
});
