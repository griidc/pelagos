var $ = jQuery.noConflict();

//FOUC preventor
$("html").hide();

$(document).ready(function(){
    "use strict";
    $("#remotelyHostedDatasetsTable").pelagosDataTable({
        "createdRow": function(row, data, dataIndex) {
            // Color all 4xx and 5xx errors rows red.
            if (/[45][0-9]{2}/.test(data.datasetSubmission.datasetFileUrlStatusCode)) {
                $(row).addClass("error-row");
            }
        }
    });

    $("html").show();

    $("label").next("input[required],textarea[required],select[required]").prev().addClass("emRequired");

    // generic noty
    var n = noty(
    {
        layout: "top",
        theme: "relax",
        type: success,
        text: data,
        animation: {
            open: "animated fadeIn", // Animate.css class names
            close: "animated fadeOut", // Animate.css class names
            easing: "swing", // unavailable - no need
            speed: 500 // unavailable - no need
        }
    });

    $("#updateButton").button().click(function(){

        $.ajax({
            type: "POST",
            url: Routing.generate("pelagos_app_ui_remotelyhosteddatasets_post", {udi : $("#udiInput").val().trim()}),
            // common noty
        }).done(function(data, textStatus, jqXHR){
            //return informative message for 202 code
            if (202 === jqXHR.status) {
                // modal: false, timeout: 4000
                n.modal = false;
                n.timeout = 4000;
            } else {
                unset(n);
                //reset table
                $("#remotelyHostedDatasetsTable").pelagosDataTable();
            }
        }.fail(function(data, textStatus, jqXHR){
                n.modal = true;
                n.type = warning;
        )};
    };

    //enable/disable button on field input
    $("#udiInput").on("input", function() {
        $("#urlText").text("");
        //16 is the length of an UDI
       if (16 !== $(this).val().trim().length) {
           $("#urlDiv").hide();
           $("#updateButton").button({
              disabled : true
           });
       } else {
           $("#updateButton").button({
              disabled : false
           });
            //get Dataset URL
           $.ajax({
               url: Routing.generate("pelagos_app_ui_remotelyhosteddatasets_geturl", {udi: $("#udiInput").val().trim()}),
           }).done(function(data, textStatus, jqXHR){
               $("#urlDiv").show();
               $("#urlText").text(data);
           });
       }
    });

});

(function($) {
    "use strict";
    $.fn.pelagosDataTable = function(options) {

        //clear the table when to prevent stale data
        $("#remotelyHostedDatasetsTable").DataTable().destroy();

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
                    },
                    {
                        "render": function (data, type, row) {
                            if (data) {
                                return data.date.replace(/\.\d+$/,"") + data.timezone;
                            } else {
                                return null;
                            }
                        },
                        "targets": 5

                    }
                ]
            }, options
            )
        );

    };
}(jQuery));

