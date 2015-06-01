var $ = jQuery.noConflict();

var base_path;

$(document).ready(function()
{
    $('#personForm').pelagosForm();
    
    base_path = $('div[base_path]').attr('base_path');
    $("#tabs").tabs({ heightStyle: "content" });
    
    // Bind the event.
    $(window).hashchange(hashchanged);
    
    // Trigger the event (useful on page load).
    hashchanged();
});

function hashchanged(){
    var hash = location.hash.replace( /^#/, '' );
    console.log(hash);
    populatePerson(hash);
}

function populatePerson(hash)
{
    $('#firstName').val('Mickel');
    $('#lastName').val('van den Eijnden');
    $('#emailAddress').val('michael.vandeneijnden@tamucc.edu');
}