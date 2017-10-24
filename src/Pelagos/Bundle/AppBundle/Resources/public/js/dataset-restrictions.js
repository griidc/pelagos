var $ = jQuery.noConflict();
$(document).ready(function(){
    "use strict";
    $("#datasetRestrictionsTable").pelagosDataTable();
});

function restrictionChange(id) {
    $.ajax({
        type: "POST",
        url: Routing.generate("pelagos_datasetrestrictions",{"id": id}),
        data: {
            "restrictions": $('#selectRestriction').val()
        },
        success: function () {
            $("#datasetRestrictionsTable").DataTable().ajax.reload();
        }
    });
}

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
                        "targets": [ 0 ],
                        "visible": false,
                        "searchable": false
                    },
                    {
                        "render": function ( data, type, row ) {
                            if (data === 'Restricted') {
                                return "<select id='selectRestriction' onchange='restrictionChange(" + row.datasetSubmission.id +")'>" +
                                    "<option value='Restricted'>" + data + "</option>" +
                                    "<option value='None'> None </option>"+
                                    "</select> " ;
                            } else {
                                return "<select id='selectRestriction' onchange='restrictionChange(" + row.datasetSubmission.id +")'>" +
                                    "<option value='None'>"+ data + " </option>"+
                                    "<option value='Restricted'> Restricted </option>" +
                                    "</select> " ;
                            }

                        },
                        "targets": 2

                    }
                ]
            }, options
            )
        );

    };
}(jQuery));
