var $ = jQuery.noConflict();

$(document).ready(function() {
    $('#tabs').tabs();
});

function clearStatusMessages() {
    $( "#messages" ).fadeOut( "slow", function() {
    });
}
