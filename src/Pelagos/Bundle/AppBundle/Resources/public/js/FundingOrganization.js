var $ = jQuery.noConflict();

$(document).ready(function()
{
    "use strict";

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
        method: "PUT",
        multipart: false,
        done: function (e, data) {
            $("#fundingOrganizationLogo img").attr("src", data.url + "?" + new Date().getTime());
        }
    }).prop("disabled", !$.support.fileInput)
    .parent().addClass($.support.fileInput ? undefined : "disabled");
});
