$(document).ready(function(){
    "use strict";

    $(".entityTable").pelagosDataTable({
        "ajax": pelagosBasePath + "/services/entity/ResearchGroup?properties=name,url,phoneNumber,deliveryPoint,city,administrativeArea,postalCode,country,creationTimeStamp,creator,modificationTimeStamp,modifier,description,emailAddress",
        "columns": [
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
            { "data": "emailAddress" },
        ],
        "headers": [
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
            "Email Address",
        ]
    });
});
