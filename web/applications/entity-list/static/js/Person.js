$(document).ready(function(){
    "use strict";

     $(".entityTable").pelagosDataTable({
        "ajax": pelagosBasePath + "/services/entity/Person",
        "columns": [
            { "data": "id" },
            { "data": "firstName" },
            { "data": "lastName" },
            { "data": "emailAddress" },
            { "data": "creationTimeStamp" },
            { "data": "creator" },
            { "data": "modificationTimeStamp" },
            { "data": "modifier" }
        ],
        "headers": [
            "ID",
            "First Name",
            "Last Name",
            "Email Address",
            "Creation Time Stamp",
            "Creator",
            "Last Modified Time Stamp",
            "Modifier"
        ],
        "columnDefs": [ { "searchable": false, "targets": [ 0, 4, 5, 6, 7 ] } ],
        "order": [[ 6, "desc" ]]
     });
    $(".entityTable").attr("deletable", "");
});
