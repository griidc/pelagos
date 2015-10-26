var $ = jQuery.noConflict();
$(document).ready(function(){
    "use strict";

    $(".entityTable").pelagosDataTable({
        "ajax": pelagosBasePath + "/services/entity/FundingCycle",
        "columns": [
            { "data": "name" },
            { "data": "fundingOrganization.name" },
            { "data": "description" },
            { "data": "url" },
            { "data": "startDate" },
            { "data": "endDate" },
            { "data": "creationTimeStamp" },
            { "data": "creator" },
            { "data": "modificationTimeStamp" },
            { "data": "modifier" },
            { "data": "fundingOrganization.id" }
        ],
        "headers": [
            "Name",
            "Funding Organization",
            "Description",
            "URL",
            "Starts",
            "Ends",
            "Created On",
            "Creator",
            "Last Modified",
            "Modifier By"
        ],
        "order": [[8, "desc" ]],
        "columnDefs": [
            {
                "targets": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                "searchable": false
            },
            {
                "targets": [10],
                "visible": false
            }
        ],
        "canDelete": userIsLoggedIn
    });
    $(".entityTable").attr("deletable", "");
});
