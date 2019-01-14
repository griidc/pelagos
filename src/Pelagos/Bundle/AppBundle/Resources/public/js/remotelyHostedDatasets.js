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
            url: Routing.generate("pelagos_app_ui_remotelyhosteddatasets_post", {udi : $("#udiInput").val().trim()}),
        }).done(function(data, textStatus, jqXHR){
            var messageType = "success";
            //return informative message for 202 code
            if (202 === jqXHR.status ) {
                messageType = "warning";
            } else {
                //reset table
                $("#remotelyHostedDatasetsTable").pelagosDataTable();
            }
            var n = noty(
            {
                layout: "top",
                theme: "relax",
                type: messageType,
                text: data,
                timeout: 4000,
                modal: false,
                animation: {
                    open: "animated fadeIn", // Animate.css class names
                    close: "animated fadeOut", // Animate.css class names
                    easing: "swing", // unavailable - no need
                    speed: 500 // unavailable - no need
                }
            });
        });
    });

    //enable/disable button on field input
    $("#udiInput").on("input", function() {
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
                    }
                ]
            }, options
            )
        );

    };
}(jQuery));

