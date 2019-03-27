var $ = jQuery.noConflict();

$(document).ready(function () {
    "use strict";
    var pageSize = 10;
    var count = $("#count").attr("data-content");
    var urlParts = window.location.search.split("page=");
    var startPage = Number(urlParts[1]);

    //Setting value of page number to 1, for new search
    $("#searchForm").submit(function () {
        $("#pageNo").val("1");
    });

    if (count > pageSize) {
        var pageCount = Math.ceil(count / pageSize);
        var url = Routing.generate("pelagos_app_ui_searchpage_default") + "?query=" + $("#search").val() + "&page=";

        $('#search-pagination').bootpag({
            total: pageCount,
            page: startPage,
            maxVisible: 5,
            leaps: true,
            firstLastUse: true,
            first: "←",
            last: "→",
            wrapClass: "pagination",
            activeClass: "active",
            disabledClass: "disabled",
            nextClass: "next",
            prevClass: "prev",
            lastClass: "last",
            firstClass: "first",
            href: url + "{{number}}"
        });
    }
});
