$(document).ready(function()
{
    "use strict";
    $(".entityForm[entityType=\"ResearchGroup\"] [name=\"logo\"]").on("logoChanged", function ()
    {
        if ($(this).attr("mimeType") !== "application/x-empty") {
            $("#researchGroupLogo").html("<img src=\"data:" + $(this).attr("mimeType") + ";base64," + $(this).attr("base64") + "\">");
        }
    });

    $(".entityForm[entityType=\"PersonResearchGroup\"]").on("entityDelete", function (event, deleteId)
    {
        $("#leadership tr[PersonResearchGroupId=\"" + deleteId + "\"]")
        .animate({ height: "toggle", opacity: "toggle" }, "slow", function() {
            $(this).slideUp("fast", function() {
                $(this)
                .remove();
            });
        });

    });

    $("#tabs")
        .tabs({ heightStyle: "content" })
        .tabs("disable", 1);

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
            $("#researchGroupLogo img").attr("src", data.url);
        }
    }).prop("disabled", !$.support.fileInput)
    .parent().addClass($.support.fileInput ? undefined : "disabled");
});
