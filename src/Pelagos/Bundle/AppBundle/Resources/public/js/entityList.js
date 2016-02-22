(function($) {
    "use strict";
    $.fn.pelagosDataTable = function(options) {

        var entityType = $(this).attr("entityType");

        var self = this;

        var listButtons = '<td class="buttons">' +
                          "<div>" +
                          '<button id="button_detail">View ' + entityType + " Details</button>&nbsp;" +
                          '<button id="button_delete">Delete ' + entityType + "</button>&nbsp;" +
                          '<span id="selection_comment"><i>For additional options, please make a selection above.</i></span>' +
                          "</div></td>";

        $(this).find("tfoot > tr").append(listButtons);
        $(".buttons").attr("colspan", $(this).find("th").length);

        var table = $(this).DataTable($.extend(true, {
                "lengthMenu": [ [25, 40, 100, -1], [25, 50, 100, "Show All"] ],
                "deferRender": false,
                "search": {
                    "caseInsensitive": true
                },
                "select": "single"
            }, options)
        );

        $("#button_detail")
        .button({
            disabled: true
        })
        .click(function () {
            var id = table.row(".selected").data().id;
            var url = $(self).attr("viewinterface") + '/' + id;
            window.open(url, "_blank");
        });

        $("#button_delete")
        .button({
            disabled: true
        })
        .click(function () {
            var id = table.row(".selected").data().id;
            var deleteURL = table.row(".selected").data()._links.delete.href;
            var msg = "You are about to remove a " + entityType + ".";
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
                    url: deleteURL,
                    method: "DELETE"
                }).done(function () {
                    $(".selected").fadeOut("slow", function () {
                        table.row(".selected").remove().draw(false);
                        $("#button_delete").button("option", "disabled", "true");
                        $("#button_detail").button("option", "disabled", "true");
                        $("#selection_comment").fadeIn();
                    });
                }).fail(function (xhr) {
                    var jsonError = xhr.responseJSON.message;
                    showDialog("Error", jsonError);
                });
            });
        });

        table.on("deselect", function ()
        {
            $("#button_detail").button("option", "disabled", true);
            $("#button_delete").button("option", "disabled", true);
            $("#selection_comment").show();
        });

        table.on("select", function ( e, dt, type, indexes)
        {
            if ( type === 'row' ) {
                if (typeof table.row( indexes ).data()._links.delete === 'undefined') {
                    $("#button_delete").button("option", "disabled", true);
                } else {
                    $("#button_delete").button("option", "disabled", false);
                }
                $("#button_detail").button("option", "disabled", false);
                $("#selection_comment").hide();
            }
        });
        return table;
    };
}(jQuery));

