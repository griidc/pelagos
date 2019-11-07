var $ = jQuery.noConflict();
$(document).ready(function(){
  "use strict";

    var entityTable = $(".entityTable");
    if (entityTable.length) {
        entityTable.pelagosDataTable();
    }

    // Clear from previous use or back button use.
    // Calling the .change() triggers whatever is watching that
    // filed for changes, in this case something that enables/disables
    // the buttons.
    $("#udiReview").val("").change();
});

(function($) {
  "use strict";
  $.fn.pelagosDataTable = function(options) {

    if (typeof options === "undefined") {
      options = {};
    }

    if (typeof options.columnDefs === "undefined") {
      options.columnDefs = [];
      options.columnDefs.push({
        "render": function (data, type, row) {
          if (type === "display") {
            // Escape potentially dangerous content.
            return data.replace(/[^0-9A-Za-z ]/g, function(c) {return "&#" + c.charCodeAt(0) + ";";});
          } else {
            return data;
          }
        },
        "targets": "_all"
      });
    }

    var columnDefinitions = $(this).data("columnDefinitions");
    if (typeof columnDefinitions !== "undefined") {
      $.merge(options.columnDefs, columnDefinitions);
    }

    var self = this;

    $(this).find(".buttons").attr("colspan", $(this).find("th").length);

    var table = $(this).DataTable($.extend(true, {
          "deferRender": false,
          "search": {
            "caseInsensitive": true
          },
          "select": "single"
        }, options)
    );

    // Copy the UDI from the current row selected to the input.
    table.on("select", function (e, dt, type, indexes) {
        if (type === "row") {
            var datasetUdi = table.rows(indexes).data().pluck("udi")[0];
            $("#udiReview").val(datasetUdi).change();
        }
    });

    // Feels more intuitive this way, imho.
    table.on("deselect", function (e, dt, type, indexes) {
        if (type === "row") {
            $("#udiReview").val("").change();
        }
    });

    return table;
  };
}(jQuery));
