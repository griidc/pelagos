var $ = jQuery.noConflict();

$(document).ready(function()
{
    formValidator = $("#personForm").validate({
        submitHandler: function(form) {
            var data = getFormJSON($('form'));
            savePerson(data.firstName, data.lastName, data.eMailAddress)
        }
    });
    
    $('#btnSave').button();
    
    $('#btnReset').button().click(function() {
        formValidator.resetForm();
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
});

function savePerson(firstName, lastName, eMailAddress)
{
    var url = "/pelagos/dev/mvde/services/person/" + firstName + "/" + lastName + "/" + eMailAddress;
    var title = "";
    var messsage = "";
    $.ajax({
        type: 'PUT',
        url: url,
        // Optionally enforce JSON return, in case a status 200 happens, but no JSON returns
        //dataType: 'json'
    })
    .done(function(json) {
        if (json.code == 200) {
            title = "Success!";
            message = json.message;
            $('#btnReset').click();
        } else {
            title = "Error!";
            message = "Something went wrong!<br>Didn't receive the correct success message!";
            message += '<br>Please contact <a href="mailto:griidc@gomri.org&subject=Person%20Form">griidc@gomri.org</a>';
        }
    })
    .fail(function(response) {
        json = response.responseJSON;
        if (typeof response.responseJSON == 'undefined') {
            json = {};
            json['code'] = response.status;
            json['message'] = response.statusText;
        }
        title = "Error!";
        message = "ERROR:" + json.code + "<br>" + json.message;
    })
    .always(function(json) {
        $('#personFormDialog').html(message);
        $('#personFormDialog').dialog( 'option', 'title', title).dialog('open');
    })
}

function getFormJSON(formSelector)
{
    var data = {};
    formSelector.serializeArray().map(function(x){data[x.name] = x.value;}); 
    return data;
}