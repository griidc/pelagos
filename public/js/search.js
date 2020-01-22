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

    let searchForm = $("#searchForm");
    //Setting value of page number to 1, for new search
    searchForm.submit(function () {
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
    let urlPelagos = Routing.generate("pelagos_app_ui_searchpage_default") + "?";
    $(".facet-aggregation").change(function () {
        $("#resgrp-facet :checkbox:checked").each(function () {
            rgIdsArray.push($(this).attr("id"));
        });

        $("#resgrp-facet :checkbox:not(:checked)").each(function () {
            if (rgIdsArray.includes(($(this).attr("id")))) {
                delete rgIdsArray[$(this).attr("id")];
            }
        });

        if (rgIdsArray.length > 0) {
            parsed.resGrp = rgIdsArray.join(",");
        } else if (rgIdsArray.length === 0) {
            delete parsed.resGrp;
        }

        $("#fundorg-facet :checkbox:checked").each(function () {
            foIdsArray.push($(this).attr("id"));
        });

        $("#fundorg-facet :checkbox:not(:checked)").each(function () {
            if (foIdsArray.includes(($(this).attr("id")))) {
                delete foIdsArray[$(this).attr("id")];
            }
        });

        if (foIdsArray.length > 0) {
            parsed.fundOrg = foIdsArray.join(",");
        } else if (foIdsArray.length === 0) {
            delete parsed.fundOrg;
        }

        $("#status-facet :checkbox:checked").each(function () {
            statusArray.push($(this).attr("id"));
        });

        $("#status-facet :checkbox:not(:checked)").each(function () {
            if (statusArray.includes(($(this).attr("id")))) {
                delete statusArray[$(this).attr("id")];
            }
        });

        if (statusArray.length > 0) {
            parsed.status = statusArray.join(",");
        } else if (statusArray.length === 0) {
            delete parsed.status;
        }
        window.location = getUrl(urlPelagos, parsed);
    });

    if (count > pageSize) {
        delete parsed.page;
        $("#search-pagination").bootpag({
            total: Math.ceil(count / pageSize),
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
            href: getUrl(urlPelagos, parsed) + "&page=" + "{{number}}"
        });

        $(".next").click(function (e) {
           e.preventDefault();
        });

        $(".pagination.bootpag").addClass("justify-content-center");

        $(".pagination.bootpag li").each(function () {
            $(this).addClass("page-item");
        })

        $(".pagination.bootpag li a").each(function () {
            $(this).addClass("page-link");
        })
    }

    // set up DatePickers
    $("#collectionStartDate").datepicker({
        dateFormat: "yy-mm-dd",
        changeMonth: true,
        changeYear: true,
        numberOfMonths: 1,
        stepMonths: 1,
        showButtonPanel: false,
        onClose: function(selectedDate) {
            $("#collectionEndDate").datepicker("option", "minDate", selectedDate);
        }
    });

    $("#collectionEndDate").datepicker({
        dateFormat: "yy-mm-dd",
        changeMonth: true,
        changeYear: true,
        numberOfMonths: 1,
        stepMonths: 1,
        showButtonPanel: false,
        onClose: function(selectedDate) {
            $("#collectionStartDate").datepicker("option", "maxDate", selectedDate);
        }
    });

    // jQuery.validator.addMethod("trueISODate", function(value, element) {
    //     var regPattern = /^([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))$/
    //     return this.optional(element) || ((Date.parse(value)) && regPattern.test(value));
    // });

    // searchForm.validate({
    //     rules: {
    //         collectionStartDate: "trueISODate",
    //         collectionEndDate: "trueISODate",
    //     },
    //     messages: {
    //         collectionStartDate: "Collection Start Date is not a valid ISO date",
    //         collectionEndDate: "Collection End Date is not a valid ISO date",
    //     },
    //     ignore: ".ignore,.prototype",
    //     submitHandler: function (form) {
    //         if ($(".ignore").valid()) {
    //             form.submit();
    //         }
    //     }
    // });
    
    $(".disabled").click(function (e) {
        e.preventDefault();
    });

    $("#collection-start-btn").click(function (e) {
        $("#collectionStartDate").datepicker('show');
    });

    $("#collection-end-btn").click(function (e) {
        $("#collectionEndDate").datepicker('show');
    });
});

function getUrl(urlPelagos, parsed) {
    return urlPelagos + Object.keys(parsed).map(key => key + "=" + parsed[key]).join("&");
}

