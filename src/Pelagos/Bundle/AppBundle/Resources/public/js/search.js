var $ = jQuery.noConflict();

$(document).ready(function () {
    "use strict";
    var pageSize = 10;
    var count = $("#count").attr("data-content");
    var urlParts = window.location.search.split("&");
    var startPage = getPageNo(urlParts);
    if (urlParts.length > 2) {
        var facetIds = getFacetIds(urlParts);
        var rgId = facetIds["rgId"];
        var foId = facetIds["foId"];
    }

    //Setting value of page number to 1, for new search
    $("#searchForm").submit(function () {
        $("#pageNo").attr("disabled", true);
    });

    if (rgId) {
        rgId = rgId.split(",");
        if (rgId.length > 0) {
            $.each(rgId, function (k, v) {
                $("#" + rgId[k]).attr("checked", true);
            });
        }
    }

    if (foId) {
        foId = foId.split(",");
        if(foId.length > 0) {
            $.each(foId, function (k, v){
                $("#" + foId[k]).attr("checked", true);
            });
        }
    }

    var rgIdsArray = [];
    var foIdsArray = [];
    var rgIds = "";
    var foIds = "";

    $("#applyBtn").click(function () {

        var urlPelagos = Routing.generate("pelagos_app_ui_searchpage_default") + "?query=" + $("#searchBox").val();

        $("#resgrp-facet :checkbox:checked").each(function () {
            rgIdsArray.push($(this).attr("id"));
        });

        if (rgIdsArray.length > 0) {
            rgIds = rgIdsArray.join(",");
        }
        $("#fundorg-facet :checkbox:checked").each(function () {
            foIdsArray.push($(this).attr("id"));
        });
        if (foIdsArray.length > 0) {
            foIds = foIdsArray.join(",");
        }

        if (foIds && rgIds) {
            window.location = urlPelagos  + "&fundOrg=" + foIds + "&resGrp=" + rgIds + "&page=" + startPage;
        } else if (rgIds) {
            window.location = urlPelagos + "&resGrp=" + rgIds + "&page=" + startPage;
        } else if (foIds) {
            window.location = urlPelagos + "&fundOrg=" + foIds + "&page=" + startPage;
        } else {
            window.location = urlPelagos;
        }

    });
    
    $("#resetBtn").click(function () {
        $("input:checkbox").removeAttr("checked");
        $("#pageNo").attr("disabled", true);
    });

    if (count > pageSize) {
        var url = document.location.href;
        var pageCount = Math.ceil(count / pageSize);
        var arr = url.split('page=');

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
            href: arr[0] + "page=" + "{{number}}"
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

    $(".disabled").click(function (e) {
        e.preventDefault();
    })
});

function getPageNo(urlParts) {
    var pageNo = 1;
    if (urlParts.length > 2) {
        pageNo = Number((urlParts[urlParts.length-1]).split("page=")[1]);
    }
    return pageNo;
}

function getFacetIds(urlParts) {
    if (urlParts.length > 3) {
        var foId = urlParts[1].split("fundOrg=")[1];
        var rgId = urlParts[2].split("resGrp=")[1];
    } else {
        var rgId = urlParts[1].split("resGrp=")[1];
        var foId = urlParts[1].split("fundOrg=")[1];
    }
    return {"foId": foId, "rgId": rgId};
}
