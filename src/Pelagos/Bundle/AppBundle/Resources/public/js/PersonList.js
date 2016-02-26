var $ = jQuery.noConflict();
$(document).ready(function(){
    "use strict";

    $(".entityTable").pelagosDataTable({
        "order": [[ 6, "desc" ]],
        "columnDefs": [
            {
                "targets": [ 4, 5, 6, 7 ],
                "searchable": false
            }
        ]
    });
});
