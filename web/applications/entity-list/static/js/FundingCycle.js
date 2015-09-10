$(document).ready(function(){

     $('.entityTable').pelagosDataTable({
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
        ]
     });
});
