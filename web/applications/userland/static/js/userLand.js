var $ = jQuery.noConflict();

var base_path;

$(document).ready(function()
{
    //$('#personForm').editableForm();
    
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

function populatePerson(PersonID)
{
    $('#personForm').trigger("reset");
    $.get(base_path+"/services/person/"+PersonID)
    .done(function( data ) {
        $('#personForm').fillForm(data.data);
    })
    .fail(function( data ) {
        console.debug('fail');
    })
    .always(function( data ) {
        
    });
}