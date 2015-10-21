$(document).ready(function(){
    "use strict";

    $(".entityTable").pelagosDataTable({
        "ajax": pelagosBasePath + "/services/entity/ResearchGroup?properties=id,name,fundingCycle,creationTimeStamp,modificationTimeStamp,creator,modifier",
        "columns": [
            { "data": "id" },
            { "data": "name" },
            { "data": "fundingCycle.name" },
            { "data": "fundingCycle.fundingOrganization.name" },
            { "data": "creationTimeStamp" },
            { "data": "modificationTimeStamp" },
            { "data": "creator" },
            { "data": "modifier" }
        ],
        "headers": [
            "Id",
            "Research Group Name",
            "Funding Cycle Name",
            "Funding Organization Name",
            "Created",
            "Modified",
            "Created By",
            "Modified By"
        ],
        "order": [[ 5, "desc" ]],
        "columnDefs": [
            {
                "targets": [ 0 ],
                "visible": false,
                "searchable": false
            },
            {
                "targets": [ 2, 3, 4, 5, 6, 7 ],
                "searchable": false
            }
        ]
    });
    $(".entityTable").attr("deletable", "");
});
