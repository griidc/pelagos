(function($) {
    "use strict";
    $.fn.pelagosDataTable = function(options) {

        var entityType = $(this).attr("entityType");

        var self = this;

        $.each(options.headers, function() {
            $(self).find("thead > tr").append("<th>" + this + "</th>");
        });

        var listButtons = '<td class="buttons">' +
                          "<div>" +
                          '<button id="button_detail">View ' + entityType + ' Details</button>&nbsp;' +
                          '<button id="button_delete">Delete ' + entityType + ' Details</button>&nbsp;' +
                          '<span id="selection_comment"><i>For additional options, please make a selection above.</i></span>' +
                          "</div></td>"

        $(this).find("tfoot > tr").append(listButtons);

        $(".buttons").attr("colspan", options.headers.length);

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
            if ((options.canDelete) && $(this).closest("table").is("[deletable]")) {
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
                        var jsonError = xhr.responseJSON.message;
                        showDialog("Error", jsonError);
                    });
                });
            }
        });

        table.on("deselect", function ()
        {
            $("#button_detail").button("option", "disabled", true);
            if ((options.canDelete) && $(this).closest("table").is("[deletable]")) {
                $("#button_delete").button("option", "disabled", true);
            }
            $("#selection_comment").fadeIn();
        });

        table.on("select", function ()
        {
            $("#button_detail").button("option", "disabled", false);
            if ((options.canDelete) && $(this).closest("table").is("[deletable]")) {
                $("#button_delete").button("option", "disabled", false);
            }
            $("#selection_comment").fadeOut();
        });

        return table;
    };
}(jQuery));

