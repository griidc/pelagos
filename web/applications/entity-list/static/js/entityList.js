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
        .click( function ( ) {
            var id = table.row(".selected").data().id;
            $.when(confirmDialog(id)).done(function() {
                $.ajax({
                    url: pelagosBasePath + "/services/entity/" + entityType + "/" + id,
                    method: "DELETE"
                }).done(function () {
                    $('.selected').fadeOut('slow', function () {
                        table.row('.selected').remove().draw( true );
                        $('#button_delete').button('option', 'disabled', 'true');
                        $('#button_detail').button('option', 'disabled', 'true');
                        $("#selection_comment").fadeIn();
                    });
                }).fail( function ( xhr, textStatus, errorThrown) {
                    var msg = "Could not delete due to reason: ";
                    $( '<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>' + msg + errorThrown + '</p>' ).dialog({
                        resizable: false,
                        height:'auto',
                        modal: true,
                        title: 'Error Encountered'
                       });
                });
            });
        });

        $(this).find("tbody").on("click", "tr", function ()
        {
            if ($(this).hasClass("selected")) {
                $(this).removeClass("selected");
                $("#button_detail").button("option", "disabled", true);
                $("#button_delete").button("option", "disabled", true);
                $("#selection_comment").fadeIn();
            } else {
                table.$("tr.selected").removeClass("selected");
                $(this).addClass("selected");
                $("#button_detail").button("option", "disabled", false);
                $("#button_delete").button("option", "disabled", false);
                $("#selection_comment").fadeOut();
            }
        });

    function confirmDialog(id)
    {
        return $.Deferred(function() {
            var self = this;
            $( '<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Remove Person from Research Group?</p>' ).dialog({
                resizable: false,
                height:'auto',
                modal: true,
                title: 'Please Confirm',
                buttons: {
                    "Delete?": function() {
                        $( this ).dialog( "close" );
                        self.resolve();
                    },
                    "Cancel": function() {
                        $( this ).dialog( "close" );
                        self.reject();
                    }
                }
            });
        });
    }

    };
}(jQuery));

