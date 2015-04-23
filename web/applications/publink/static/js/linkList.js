var $ = jQuery.noConflict();

$(document).ready(function() {
    $('#linkList').dataTable( {
        "ajax":"GetLinksJSON/",
        "aoColumns": [
            { "mDataProp": "del" },
            { "mDataProp": "fc" },
            { "mDataProp": "proj" },
            { "mDataProp": "udi" },
            { "mDataProp": "doi" },
            { "mDataProp": "username" },
            { "mDataProp": "created" },
        ],
        "deferRender": true,
        "createdRow": function ( row, data, index ) {
            $('td', row).eq(0).button ({ label: "Delete"}).click(function() {
                var doi = $(this).parent().children("td:nth-child(5)").text();
                var udi = $(this).parent().children("td:nth-child(4)").text();
                confirmDialog(doi,udi);
            });
        }
    });
});

function confirmDialog(doi, udi) {
    $( "#dialog-confirm" ).dialog({
      resizable: false,
      height:140,
      modal: true,
      buttons: {
        "Delete?": function() {
         console.log(doi, udi);
          $( this ).dialog( "close" );
        },
        Cancel: function() {
          $( this ).dialog( "close" );
        }
      }
    });
}
