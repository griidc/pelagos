var $ = jQuery.noConflict();
$(document).ready(function(){
    "use strict";
    $("#datasetRestrictionsTable").pelagosDataTable();
});

(function($) {
    "use strict";
    $.fn.pelagosDataTable = function(options) {

        if (typeof options === "undefined") {
            options = {};
        }

        if (typeof options.columnDefs === "undefined") {
            options.columnDefs = [];
        }

        var columnDefinitions = $(this).data("columnDefinitions");
        if (typeof columnDefinitions !== "undefined") {
            $.merge(options.columnDefs, columnDefinitions);
        }

        var self = this;

        $(this).find(".buttons").attr("colspan", $(this).find("th").length);

        var table = $(this).DataTable($.extend(true, {
                "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "Show All"] ],
                "deferRender": false,
                "search": {
                    "caseInsensitive": true
                },
                "select": "single",
                "columnDefs": [
                    {
                        "targets": [ 0, 3 ],
                        "visible": false,
                        "searchable": false
                    },
                    {
                        "render": function ( data, type, row ) {
                            return "<select>" +
                                "<option></option>" +
                                "<option value='Restricted'>Restricted</option>" +
                                "<option value='None'>None</option>"
                                "</select> " ;
                        },
                        "targets": 4

                    }
                ]
            }, options
            )
        );

        // Activate the bubble editor on click of a table cell
        $('#datasetRestrictionsTable').on( 'click', 'tbody td:not(:first-child)', function (e) {
            editor.bubble( this );
        } );

    };
}(jQuery));
