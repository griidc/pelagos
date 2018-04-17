var $ = jQuery.noConflict();
$(document).ready(function(){
  "use strict";

    var entityTable = $(".entityTable");
    if (entityTable.length) {
        entityTable.pelagosDataTable();
    }
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
    return table;
  };
}(jQuery));
