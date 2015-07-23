var $ = jQuery.noConflict();

var fundingID;

var hashchanged = function()
{
    "use strict";
    var hash = window.location.hash.replace(/^#/, "");
    populateFundingOrganization(hash);
    fundingID = hash;
};

$(document).ready(function()
{
    "use strict";
    var formValidator = $('#fundingOrganizationForm').validate({
        submitHandler: function(form) {
            //var data = $(form).getFormJSON();
            var data = new FormData(form);
            updateFundingOrganization(data,fundingID);
        }
    });
    var isLoggedIn = JSON.parse($('div[userLoggedIn]').attr('userLoggedIn'));
    if (isLoggedIn) {
        $('#fundingOrganizationForm').editableForm({
            validationURL: pelagosBasePath + '/services/fundingOrganization/validateProperty'
            
        });
    }
    // Bind the event.
    $(window).hashchange(hashchanged);
    // Trigger the event (useful on page load).
    hashchanged();
    
    $('#fundingOrganizationFormDialog').dialog({
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
});

function populateFundingOrganization(FundingOrganizationID)
{
    "use strict";
    $("#fundingOrganizationForm").trigger("reset");
    $("#fundingOrganizationLogo").html("");
    $.get(pelagosBasePath + "/services/fundingOrganization/" + FundingOrganizationID)
    .done(function(data) {
        $("#fundingOrganizationForm").fillForm(data.data);
        $("#fundingOrganizationLogo").html("<img src=\"" + pelagosBasePath + "/services/fundingOrganization/logo/" + FundingOrganizationID + "\">");
    });
}

function updateFundingOrganization(jsonData,fundingID)
{
    var theurl = pelagosBasePath + "/services/fundingOrganization/"+fundingID;
    var title = "";
    var messsage = "";
    debugger;
    $.ajax({
        type: 'PUT',
        data: jsonData,
        url: theurl,
        // Optionally enforce JSON return, in case a status 200 happens, but no JSON returns
        //dataType: 'json'
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(json) {
        if (json.code == 200) {
            title = "Success!";
            message = json.message;
            $('#fundingOrganizationForm').editableForm('reset');
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
            $('#fundingOrganizationFormDialog').html(message);
            $('#fundingOrganizationFormDialog').dialog( 'option', 'title', title).dialog('open');
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