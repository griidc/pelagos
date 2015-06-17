var $ = jQuery.noConflict();

var base_path;

var currentPerson;

$(document).ready(function()
{
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
    
    $('#personForm').editableForm();
    
    base_path = $('div[base_path]').attr('base_path');
    $("#tabs").tabs({ heightStyle: "content" });
    
    // Bind the event.
    $(window).hashchange(hashchanged);
    
    // Trigger the event (useful on page load).
    hashchanged();
    
});

function updatePerson()
{
    return $.Deferred(function() {
        var self = this;
        console.log('updated!');
        self.resolve();
    });
    
}

function hashchanged(){
    var hash = location.hash.replace( /^#/, '' );
    currentPerson = hash;
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
            $(form).editableForm('reset');
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
        }
    })
}