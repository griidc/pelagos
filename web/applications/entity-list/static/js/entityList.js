var $ = jQuery.noConflict();

(function($) {
    "use strict";
    $.fn.pelagosDataTable = function(options) {

        var entityType = $(this).attr("entityType");

        var self = this;

        $(".buttons").attr("colspan", options.headers.length);

        $.each(options.headers, function() {
            $(self).find("thead > tr").append("<th>" + this + "</th>");
        });

        var table = $(this).DataTable($.extend(true, {
                "lengthMenu": [ [15, 25, 40, 100, -1], [15, 25, 50, 100, "Show All"] ],
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
            var id = table.row(".selected").data()["id"];
            var url = pelagosBasePath + "/applications/entity/" + entityType + "/" + id;
            window.open(url, "_blank");
        });

        $(this).find("tbody").on("click", "tr", function ()
        {
            if ($(this).hasClass("selected")) {
                $(this).removeClass("selected");
                $("#button_detail").button("option", "disabled", true);
                $("#selection_comment").fadeIn();
            } else {
                table.$("tr.selected").removeClass("selected");
                $(this).addClass("selected");
                $("#button_detail").button("option", "disabled", false);
                $("#selection_comment").fadeOut();
            }
        });
    };
}(jQuery));

