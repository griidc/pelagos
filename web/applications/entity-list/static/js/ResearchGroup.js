$(document).ready(function(){
    "use strict";

    $(".entityTable").pelagosDataTable({
        "ajax": pelagosBasePath + "/services/entity/ResearchGroup?properties=id,name,url,phoneNumber,deliveryPoint,city,administrativeArea,postalCode,country,creationTimeStamp,creator,modificationTimeStamp,modifier,description,emailAddress",
        "columns": [
            { "data": "id" },
            { "data": "name" },
            { "data": "url" },
            { "data": "phoneNumber" },
            { "data": "deliveryPoint" },
            { "data": "city" },
            { "data": "administrativeArea" },
            { "data": "postalCode" },
            { "data": "country" },
            { "data": "creationTimeStamp" },
            { "data": "creator" },
            { "data": "modificationTimeStamp" },
            { "data": "modifier" },
            { "data": "description" },
            { "data": "emailAddress" }
        ],
        "headers": [
            "Id",
            "Name",
            "URL",
            "Phone Number",
            "Address",
            "City",
            "State",
            "Zip",
            "Country",
            "Created On",
            "Creator",
            "Last Modified",
            "Modifier By",
            "Description",
            "Email Address"
        ],
        "order": [[ 11, "desc" ]],
        "columnDefs": [
            {
                "targets": [ 0 ],
                "visible": false,
                "searchable": false
            }
        ]
    });
});
