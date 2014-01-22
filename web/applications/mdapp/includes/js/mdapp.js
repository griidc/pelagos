var $ = jQuery.noConflict();

$(document).ready(function() {
    $('#tabs').tabs();
});

function clearStatusMessages() {
    $( "#messages" ).fadeOut( "fast", function() {
    });
}

function clearTestGeometry() {
    document.getElementById("testGeometry").value = "";
}
