var $ = jQuery.noConflict();

$(document).ready(function () {
    "use strict";
    var pageSize = 10;
    var count = $("#count").attr("data-content");
    var urlParts = window.location.search.split("?");
    let queryParse = parseQueryString(urlParts[1]);
    let startPage = `${queryParse.page ? `${queryParse.page}` : 1}`;
    let rgId = `${queryParse.resGrp}`;
    let foId = `${queryParse.fundOrg}`;

    //Setting value of page number to 1, for new search
    $("#searchForm").submit(function () {
        $("#pageNo").attr("disabled", true);
    });

    // Research group checkbox
    if (rgId) {
        rgId = rgId.split(",");
        if (rgId.length > 0) {
            $.each(rgId, function (k, v) {
                $("#" + rgId[k]).attr("checked", true);
            });
        }
    }

    // Funding organization checkbox
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

    $(".checkbox").change(function () {

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
            window.location = urlPelagos  + "&fundOrg=" + foIds + "&resGrp=" + rgIds;
        } else if (rgIds) {
            window.location = urlPelagos + "&resGrp=" + rgIds;
        } else if (foIds) {
            window.location = urlPelagos + "&fundOrg=" + foIds;
        } else {
            window.location = urlPelagos;
        }

    });

    if (count > pageSize) {
        var url = document.location.href;
        var pageCount = Math.ceil(count / pageSize);
        var arr = url.split('&page=');

        $("#search-pagination").bootpag({
            total: pageCount,
            page: startPage,
            maxVisible: 5,
            leaps: true,
            firstLastUse: true,
            first: "←",
            last: "→",
            activeClass: "active",
            disabledClass: "disabled",
            nextClass: "next",
            prevClass: "prev",
            lastClass: "last",
            firstClass: "first",
            href: arr[0] + "&page=" + "{{number}}"
        });

        $(".next").click(function (e) {
           e.preventDefault();
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

function parseQueryString(urlParts) {
    let parsedQuery = {};

    let vars = urlParts.split("&");
    vars.forEach(function (key, value) {
        let pair = key.split("=");
        parsedQuery[pair[0]] = pair[1];
    });

    return parsedQuery;
}

