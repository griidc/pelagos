$(document).ready(function(){
    "use strict";

    $(".entityTable").pelagosDataTable({
        "ajax": pelagosBasePath + "/services/entity/FundingCycle",
        "columns": [
            { "data": "name" },
            { "data": "description" },
            { "data": "url" },
            { "data": "startDate" },
            { "data": "endDate" },
            { "data": "creationTimeStamp" },
            { "data": "creator" },
            { "data": "modificationTimeStamp" },
            { "data": "modifier" }
        ],
        "headers": [
            "Name",
            "Description",
            "URL",
            "Starts",
            "Ends",
            "Created On",
            "Creator",
            "Last Modified",
            "Modifier By"
        ],
        "order": [[7, "desc" ]],
        "columnDefs": [
            {
                "searchable": false,
                "targets": [1, 2, 3, 4, 5, 6, 7, 8]
            }
        ]
    });
});
