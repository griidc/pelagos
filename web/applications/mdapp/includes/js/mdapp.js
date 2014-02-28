var $ = jQuery.noConflict();

$(document).ready(function() {
    $('#tabs').tabs({
        active: $.cookie('activetab'),
        activate : function( event, ui ) {
            $.cookie( 'activetab', ui.newTab.index(),{
                expires : 10
            });
        }
    });
    $('#tabs table.metadata').tablesorter({
        sortList: [[2,0]],
        sortRestart : true,
        sortInitialOrder: 'asc',
        headers: {
            1: { sorter: false }
        }
    });
});

function clearStatusMessages() {
    $( "#messages" ).fadeOut( "fast", function() {
    });
}

function clearTestGeometry() {
    document.getElementById("testGeometry").value = "";
}
