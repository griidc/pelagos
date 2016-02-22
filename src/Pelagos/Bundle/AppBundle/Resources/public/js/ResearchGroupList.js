var $ = jQuery.noConflict();
$(document).ready(function(){
    "use strict";

    $(".entityTable").pelagosDataTable({
        "order": [[ 5, "desc" ]],
        "columnDefs": [
            {
                "targets": [ 0 ],
                "visible": false,
                "searchable": false
            },
            {
                "targets": [ 0, 2, 3, 4, 5, 6, 7 ],
                "searchable": false
            }
        ]
    });
    $(".entityTable").attr("deletable", "");
});
