var $ = jQuery.noConflict();

$(document).ready(function() {
    $('#linkList').dataTable( {
        "ajax":"GetLinksJSON/",
        "aoColumns": [
            { "mDataProp": "del" },
            { "mDataProp": "udi" },
            { "mDataProp": "doi" },
            { "mDataProp": "username" },
            { "mDataProp": "created" },
        ],
        "deferRender": true,
        "createdRow": function ( row, data, index ) {
            $('td', row).eq(0).button ({ label: "Delete"}).click(function() {
                console.log($(this).parent().children("td:nth-child(5)").text());
            });
        }
    });
});
