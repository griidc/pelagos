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
});

function clearStatusMessages() {
    $( "#messages" ).fadeOut( "fast", function() {
    });
}

function clearTestGeometry() {
    document.getElementById("testGeometry").value = "";
}
