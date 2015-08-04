var $ = jQuery.noConflict();

var base_path;

var currentPerson;

var userLoggedIn;

$(document).ready(function()
{
    base_path = $('div[base_path]').attr('base_path');
    
    isLoggedIn = JSON.parse($('div[userLoggedIn]').attr('userLoggedIn'));
    
    console.log(isLoggedIn);
    
    $("#tabs").tabs({ heightStyle: "content" });
    
    formValidator = $('#personForm').validate({
        submitHandler: function(form) {
            var data = $(form).getFormJSON();
            updatePerson(data,currentPerson);
        }
    });
    
    $('#personFormDialog').dialog({
        autoOpen: false,
        resizable: false,
        minWidth: 300,
        width: 'auto',
        height: 'auto',
        modal: true,
        buttons: {
            Ok: function() {
                $( this ).dialog( "close" );
            }
        }
    });
    
    //minWidth does not work properly with 'auto' width, so hack
    $('.ui-dialog').css({'min-width': '300px'});
    
    //Make form editable if Logged In
    if (isLoggedIn) {
        $('#personForm').editableForm({
            validationURL : pelagosBasePath + '/services/person/validateProperty'
        });
    }
    
    // Bind the event.
    $(window).hashchange(hashchanged);
    
    // Trigger the event (useful on page load).
    hashchanged();
    
});

var rebindHash = function ()
{
    $(window).unbind('hashchange', rebindHash);
    $(window).bind('hashchange', hashchanged);
}

var hashchanged = function(){
    var hash = window.location.hash.replace( /^#/, '' );
    
    //console.log(hash);
    if ($('form .active').length == 0) {
        populatePerson(hash);
        currentPerson = hash;
    } else {
        //window.location.hash = window.location.href + '#' + currentPerson;
        console.log ('reload!');
        console.log(currentPerson);
        if (confirm('You still have unsaved changed!\nAre you sure you want to navigate away?')) {
                $('form').editableForm('reset');
                populatePerson(hash);
                currentPerson = hash;
        } else {
            $(window).unbind('hashchange', hashchanged);
            $(window).bind('hashchange', rebindHash);
            window.location.hash =  '#' + currentPerson;
        }
    }
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

function updatePerson(jsonData,PersonID)
{
    var url = base_path + "/services/person/"+PersonID;
    var title = "";
    var messsage = "";
    $.ajax({
        type: 'PUT',
        data: jsonData,
        url: url,
        // Optionally enforce JSON return, in case a status 200 happens, but no JSON returns
        //dataType: 'json'
    })
    .done(function(json) {
        if (json.code == 200) {
            title = "Success!";
            message = json.message;
            $('#personForm').editableForm('reset');
        } else {
            title = "Error!";
            message = "Something went wrong!<br>Didn't receive the correct success message!";
            message += '<br>Please contact <a href="mailto:griidc@gomri.org&subject=userland">griidc@gomri.org</a>';
        }
    })
    .fail(function(response) {
        json = response.responseJSON;
        if (typeof response.responseJSON == 'undefined') {
            var json = {};
            json['code'] = response.status;
            json['message'] = response.statusText;
        }
        title = "Error!";
        message = json.message;
    })
    .always(function(json) {
        if (json.code != 200) {
            $('#personFormDialog').html(message);
            $('#personFormDialog').dialog( 'option', 'title', title).dialog('open');
        } else {
            //$('.noty_inline_layout_container);
            var n = $('#notycontainer').noty({
            //var n = noty({
                layout: 'top',
                text: message,
                theme: 'relax',
                animation: {
                    open: 'animated bounceIn', // Animate.css class names
                    close: 'animated fadeOut', // Animate.css class names
                    easing: 'swing', // unavailable - no need
                    speed: 500 // unavailable - no need
                },
                timeout: 3000
            });
        }
    })
}