var $ = jQuery.noConflict();

(function($) {
    "use strict";
    $.fn.createEntityForm = function() {

        var entity = $(this).attr("entity");

        var formValidator = $(this).validate({
            submitHandler: function(form) {
                var formData = new FormData(form);
                saveEntity(entity, formData);
            }
        });

        $(this).find("input, textarea").each(function() {
            var url = pelagosBasePath + "/services/entity/" + entity + "/validateProperty";

            $(this).rules("add", {
                remote: {
                    url: url
                }
            });
        });

        $(this).find("button[type='submit']").button();

        $(this).find("button[type='reset']").button().click(function() {
            formValidator.resetForm();
        });


    };
}(jQuery));


$(document).ready(function()
{
    "use strict";
    $.validator.methods._required = $.validator.methods.required;
    $.validator.methods.required = function(value, element, param)
    {
        if (typeof this.settings.rules[ $(element).attr("name") ] !== "undefined"
            && typeof this.settings.rules[ $(element).attr("name") ].remote !== "undefined") {
                return true;
            }
        return $.validator.methods._required.call(this, value, element, param);
    };

    $("form").createEntityForm();
});

/**
 * This function will send the funding organization data to the web service.
 *
 * @param FormData jsonData The form data.
 *
 * @return void
 */
function saveEntity(entity, jsonData)
{
    "use strict";
    var url = pelagosBasePath + "/services/entity/" + entity;
    var title = "";
    var message = "";
    $.ajax({
        type: "POST",
        data: jsonData,
        url: url,
        // Optionally enforce JSON return, in case a status 200 happens, but no JSON returns
        //dataType: 'json'
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(json) {
        if (json.code === 201) {
            title = "Success!";
            message = json.message;
            $("#btnReset").click();
        } else {
            title = "Error!";
            message = "Something went wrong!<br>Didn't receive the correct success message!";
            message += "<br>Please contact <a href=\"mailto:griidc@gomri.org&subject=Create%20Form\">griidc@gomri.org</a>";
        }
    })
    .fail(function(response) {
        var json = {};
        if (typeof response.responseJSON === "undefined") {
            json.message = response.statusText;
        } else {
            json = response.responseJSON;
        }
        title = "Error!";
        message = json.message;
    })
    .always(function() {
        showDialog(title, message);
    });
}

function showDialog(title, message)
{
    "use strict";
    $("<div>" + message + "</div>").dialog({
        autoOpen: true,
        resizable: false,
        minWidth: 300,
        height: "auto",
        modal: true,
        title: title,
        buttons: {
            Ok: function() {
                $(this).dialog("close");
            }
        }
    });
}
