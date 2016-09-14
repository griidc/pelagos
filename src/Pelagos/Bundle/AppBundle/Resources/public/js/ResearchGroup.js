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

    // Special stuff for Addform
    if ($(document).has(".addimg").length ? true : false) {
        var newForm = $("form[newform]");

        newForm.fadeOut();

        $(".addimg").button().click(function() {
            var addImg = $(this).fadeOut();
            var lastTr = $(newForm).closest("table").find("tr:last");

            var cloneForm = newForm
                .clone(false)
                .insertBefore(lastTr)
                .removeAttr("newform")
                .fadeIn()
                .entityForm()
                ;

            $('[name="person"]', cloneForm).select2({
                placeholder: "[Please Select a Person]",
                allowClear: true,
                ajax: {
                    dataType: 'json',
                    data: function (params) {
                        if (params.term != undefined) {
                            var query = {
                                "lastName": params.term + '*'
                            }
                        } else {
                            var query = {}
                        }
                        return query;
                    },
                    url: Routing.generate("pelagos_api_people_get_collection",
                        {
                            "_properties" : "id,firstName,lastName,emailAddress"
                        }
                    ),
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.lastName + ", " +  item.firstName + ", " + item.emailAddress,
                                    id: item.id
                                }
                            })
                        };
                    }
                }
            });

            $(cloneForm).find("#cancelButton").click(function() {
                addImg.fadeIn();
                if ($(cloneForm).closest("form").find("input[name='id']").val() === "") {
                    cloneForm
                        .fadeOut()
                        .unwrap()
                        .remove();
                }
            });

            $(cloneForm).find('button[type="submit"]').click(function() {
                $(cloneForm).one("reset", function() {
                    if ($(this).find("input[name='id']").val() !== undefined) {
                        var newEntityForm = $(this);
                        var newEntity = newEntityForm
                            .parent()
                            .wrap("<tr><td><div><p></p><p></p></div></td></tr>")
                            ;
                        addImg.fadeIn();
                    }
                });
            });
        });
    }
});
