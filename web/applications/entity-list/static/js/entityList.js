var $ = jQuery.noConflict();

var table;

(function($) {
    "use strict";
    $.fn.pelagosDataTable = function(options) {

        var entityType = $(this).attr("entityType");

        var self = this;

        $(".buttons").attr("colspan", options.headers.length);

        $.each(options.headers, function() {
            $(self).find("thead > tr").append("<th>" + this + "</th>");
        });

        table = $(this).DataTable($.extend(true, {
                "lengthMenu": [ [25, 40, 100, -1], [25, 50, 100, "Show All"] ],
                "deferRender": false,
                "search": {
                    "caseInsensitive": true
                }
            }, options)
        );

        $("#button_detail")
        .button({
            disabled: true
        })
        .click(function () {
            var id = table.row(".selected").data().id;
            var url = pelagosBasePath + "/applications/entity/" + entityType + "/" + id;
            window.open(url, "_blank");
        });

        $("#button_delete")
        .button({
            disabled: true
        })
        .click(function () {
            var id = table.row(".selected").data().id;
            var msg = "You are about to remove a " + entityType + ".";
            if ((userIsLoggedIn == 1) && $(this).closest("table").is("[deletable]")) {
                $.when(showConfirmation({
                        title: "Please confirm:",
                        message: msg,
                        buttons: {
                            "Yes": {
                                text: "Delete " + entityType
                            },
                            "No": {
                                text: "Cancel"
                            }
                        }
                    })).done(function() {
                    $.ajax({
                        url: pelagosBasePath + "/services/entity/" + entityType + "/" + id,
                        method: "DELETE"
                    }).done(function () {
                        $(".selected").fadeOut("slow", function () {
                            table.row(".selected").remove().draw(true);
                            $("#button_delete").button("option", "disabled", "true");
                            $("#button_detail").button("option", "disabled", "true");
                            $("#selection_comment").fadeIn();
                        });
                    }).fail(function (xhr) {
                        msg = "Could not delete due to reason: ";
                        var jsonError = xhr.responseJSON.message;
                        showDialog('Error', msg + jsonError);
                    });
                });
            }
        });

        $(this).find("tbody").on("click", "tr", function ()
        {
            if ($(this).hasClass("selected")) {
                $(this).removeClass("selected");
                $("#button_detail").button("option", "disabled", true);
                if ((userIsLoggedIn == 1) && $(this).closest("table").is("[deletable]")) {
                    $("#button_delete").button("option", "disabled", true);
                }
                $("#selection_comment").fadeIn();
            } else {
                table.$("tr.selected").removeClass("selected");
                $(this).addClass("selected");
                $("#button_detail").button("option", "disabled", false);
                if ((userIsLoggedIn == 1) && $(this).closest("table").is("[deletable]")) {
                    $("#button_delete").button("option", "disabled", false);
                }
                $("#selection_comment").fadeOut();
            }
        });
    };
}(jQuery));

