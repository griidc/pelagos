var $ = jQuery.noConflict();

$(document).ready(function () {
    "use strict";
    let pageSize = 10;
    let count = $("#count").attr("data-content");
    const parsed = queryString.parse(location.search);
    let startPage = `${parsed.page ? `${parsed.page}` : 1}`;
    let rgId = `${parsed.resGrp}`;
    let foId = `${parsed.fundOrg}`;
    let status = `${parsed.status}`;

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

    // Availability status checkbox
    if (status) {
        status = status.split(",");
        if(status.length > 0) {
            $.each(status, function (k, v){
                $("#" + status[k]).attr("checked", true);
            });
        }
    }


    let rgIdsArray = [];
    let foIdsArray = [];
    let statusArray = [];

    $(".facet-aggregation").change(function () {
        let urlPelagos = Routing.generate("pelagos_app_ui_searchpage_default") + "?";
        $("#resgrp-facet :checkbox:checked").each(function () {
            rgIdsArray.push($(this).attr("id"));
        });

        if (rgIdsArray.length > 0) {
            parsed.resGrp = rgIdsArray.join(",");
        }
        $("#fundorg-facet :checkbox:checked").each(function () {
            foIdsArray.push($(this).attr("id"));
        });
        if (foIdsArray.length > 0) {
            parsed.fundOrg = foIdsArray.join(",");
        }
        $("#status-facet :checkbox:checked").each(function () {
            statusArray.push($(this).attr("id"));
        });
        if (statusArray.length > 0) {
            parsed.status = statusArray.join(",");
        }

        let newQueryString = Object.keys(parsed).map(key => key + "=" + parsed[key]).join("&");
        window.location = urlPelagos + newQueryString;
    });

    if (count > pageSize) {
        let url = document.location.href;
        let pageCount = Math.ceil(count / pageSize);
        let arr = url.split("&page=");

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

