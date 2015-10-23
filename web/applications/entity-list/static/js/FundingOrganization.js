var $ = jQuery.noConflict();
$(document).ready(function(){
    "use strict";

    $(".entityTable").pelagosDataTable({
        "ajax": pelagosBasePath + "/services/entity/FundingOrganization",
        "columns": [
            { "data": "name" },
            { "data": "emailAddress" },
            { "data": "description" },
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
            { "data": "modifier" }
        ],
        "headers": [
            "Name",
            "Email Address",
            "Description",
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
            "Modifier By"
        ],
        "canDelete": userIsLoggedIn
    });
    $(".entityTable").attr("deletable", "");
});
