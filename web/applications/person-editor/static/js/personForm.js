var $ = jQuery.noConflict();

$(document).ready(function()
{
    formValidator = $("#personForm").validate({
        submitHandler: function(form) {
            if (formValidator.valid()) {
                var data = getFormJSON($('form'));
                savePerson(data.firstName, data.lastName, data.eMailAddress)
            }
        }
    });
    
    $('#btnSave').button();
    
    $('#btnReset').button().click(function() {
        formValidator.resetForm();
    });
});

function savePerson(firstName, lastName, eMailAddress)
{
    var url = "/pelagos/dev/mwilliamson/services/person/" + firstName + "/" + lastName + "/" + eMailAddress;
    $.ajax({
        type: 'PUT',
        url: url
    })
    .done(function() {
        alert("did it");
    })
    .fail(function(response) {
        if (response.status == 404) {
            alert( "NOT FOUND!" )
        }
        if (response.status == 500) {
            alert( "SERVER ERROR!" )
        }
    })
}

function getFormJSON(formSelector)
{
    var data = {};
    formSelector.serializeArray().map(function(x){data[x.name] = x.value;}); 
    return data;
}