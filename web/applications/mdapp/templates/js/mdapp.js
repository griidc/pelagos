var $ = jQuery.noConflict();

$(document).ready( function () {
    $('#tabs').tabs({
        active: $.cookie('activetab'),
        activate : function( event, ui ) {
            $.cookie( 'activetab', ui.newTab.index(),{
                expires : 10
            });
        $( $.fn.dataTable.tables( true ) ).DataTable().columns.adjust();
        }
    });

    $('table.display').dataTable( {
        "paging": true,
        "jQueryUI": true,
        "lengthMenu": [ [-1, 25, 50, 75, 100], ["All", 25, 50, 75, 100] ]
    } );
} );

function clearStatusMessages() {
    $( "#messages" ).fadeOut( "fast", function() {
    });
}

function clearTestGeometry() {
    document.getElementById("testGeometry").value = "";
}

function showLogEntries(udi) {
    $.ajax({
            "url": "{{baseUrl}}/getlog/" + udi,
            "success": function(data) {
                $('#log_content').html(data);
                $('#log_title').html("&nbsp; &nbsp; Log Entries For: " + udi);
                $('#log').show();
            }
    });

}
