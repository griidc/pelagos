var $ = jQuery.noConflict();

$(document).ready(function () {
    "use strict";
    var pageSize = 10;
    var count = $("#count").attr("data-content");
    var urlParts = window.location.search.split("&");
    var startPage = getPageNo(urlParts);
    if (urlParts[2]) {
        var rgId = urlParts[2].split("rgId=")[1];
    }
    var url = Routing.generate("pelagos_app_ui_searchpage_default") + "?query=" + $("#searchBox").val() + "&page=";


    //Setting value of page number to 1, for new search
    $("#searchForm").submit(function () {
        $("#pageNo").attr("disabled", true);
    });

    var resGrpFacetCheckbox = $("#resgrp-facet :input[type='checkbox']");

    if (resGrpFacetCheckbox.attr("id") === rgId) {
        resGrpFacetCheckbox.attr("checked", true);
    }

    resGrpFacetCheckbox.change(function () {
        if ($(":input[type='checkbox']").is(":checked")) {
            window.location = url + "1&rgId="+ $(this).attr("id");
        } else {
            window.location = url +"1";
        }
    });

    if (count > pageSize) {
        var pageCount = Math.ceil(count / pageSize);

        $("#search-pagination").bootpag({
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

    // load qTip descriptions
    $(".groupName").hover().each(function() {
        $(this).qtip({
            content: {
                text: $.trim($(this).next(".tooltiptext").text())
            }
        });
    });
});

function getPageNo(urlParts) {
    var pageNo = 1;
    if (urlParts[1]){
        pageNo = Number(urlParts[1].split("page=")[1]);
    }
    return pageNo;
}
