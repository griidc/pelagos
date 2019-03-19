var $ = jQuery.noConflict();

$(document).ready(function () {
    "use strict";
    var pageSize = 10;
    var count = $("#count").attr("data-content");

    if (count > pageSize) {
        var pageCount = Math.ceil(count / pageSize);

        $("#search-pagination").twbsPagination({
            totalPages: pageCount,
            visiblePages: 6,
            next: 'Next',
            prev: 'Prev',
            href: true,
            onPageClick: function (event, page) {
                //fetch content and render here
                var url = Routing.generate("pelagos_app_ui_searchpage_default") + "?query=" + $("#search").val();
                $(this).find("li a").attr("href", url);
            }
        });
    }
});
