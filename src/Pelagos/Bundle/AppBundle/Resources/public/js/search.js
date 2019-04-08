var $ = jQuery.noConflict();

$(document).ready(function () {
    "use strict";
    var pageSize = 10;
    var count = $("#count").attr("data-content");
    var urlParts = window.location.search.split("&");
    var startPage = getPageNo(urlParts);
    if (urlParts[2]) {
        var rgId = urlParts[2].split("resGrp=")[1];
        var foId = urlParts[2].split("fundOrg=")[1];
    }
    var url = Routing.generate("pelagos_app_ui_searchpage_default") + "?query=" + $("#searchBox").val() + "&page=" + startPage;


    //Setting value of page number to 1, for new search
    $("#searchForm").submit(function () {
        $("#pageNo").attr("disabled", true);
    });


    $("#" + rgId).attr("checked", true);
    $("#" + foId).attr("checked", true);

    $("#resgrp-facet :checkbox:checked");
    var rgIdsArray = [];
    var foIdsArray = [];
    var rgIds = "";
    var foIds = "";

    $("#applyBtn").click(function () {

        $("#resgrp-facet :checkbox:checked").each(function () {
            rgIdsArray.push($(this).attr("id"));
        });

        if (rgIdsArray.length !== 0) {
            rgIds = rgIdsArray.join(",");
        }
        $("#fundorg-facet :checkbox:checked").each(function () {
            foIdsArray.push($(this).attr("id"));
        });
        if (foIdsArray.length !== 0) {
            foIds = foIdsArray.join(',');
        }

        if (foIds && rgIds) {
            window.location = url + "&fundOrg=" + foIds + "&resGrp=" + rgIds;
        } else if (rgIds) {
            window.location = url + "&resGrp=" + rgIds;
        } else if (foIds) {
            window.location = url + "&fundOrg=" + foIds;
        }
    });
    
    $("#resetBtn").click(function () {
        $("input:checkbox").removeAttr("checked");
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
