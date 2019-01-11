var $ = jQuery.noConflict();

//FOUC preventor
$("html").hide();

$(document).ready(function(){
    "use strict";
    $("#remotelyHostedDatasetsTable").pelagosDataTable();

    $("html").show();

    $("label").next("input[required],textarea[required],select[required]").prev().addClass("emRequired");

    $("#updateButton").button().click(function(){

        $.ajax({
            type: "POST",
            url: Routing.generate("pelagos_app_ui_remotelyhosteddatasets_post"),
            data: {
                udi: $("#udiInput").val().trim()
            },
        }).done(function(data, textStatus, jqXHR){
            alert();
        }).fail(function(jqXHR, textStatus, errorThrown){
            alert("FAIL");
        });
    });

    //enable/disable button on field input
    $("#udiInput").on("input", function() {
       if ("" === $(this).val().trim()) {
           $("#updateButton").button({
              disabled : true
           });
       } else {
           $("#updateButton").button({
              disabled : false
           });
       }
    });

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
                        "targets": 0,
                        "visible": false,
                        "searchable": false
                    }
                ]
            }, options
            )
        );

    };
}(jQuery));
