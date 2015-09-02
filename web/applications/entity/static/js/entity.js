var $ = jQuery.noConflict();

$(document).ready(function()
{
    "use strict";
    var urlParts = window.location.href.split("/");
    var lastPart = urlParts.pop();
    var entityType = null;
    var entityId = null;
    if (isNaN(parseInt(lastPart))) {
        entityType = lastPart;
    } else {
        entityId = lastPart;
        entityType = urlParts.pop();
        if ($("#" + entityType + "Logo").length) {
            populateEntityLogo(entityType, entityId);
        }
    }
    $("#" + entityType + "Form").validate({
        submitHandler: function(form) {
            var data = new FormData(form);
            updateEntity(entityType, entityId, data);
        }
    });
    var isLoggedIn = JSON.parse($("div[userLoggedIn]").attr("userLoggedIn"));
    if (isLoggedIn) {
        $("#" + entityType + "Form").editableForm({
            validationURL: pelagosBasePath + "/services/entity/" + entityType + "/validateProperty"
        });
        if (entityId === null) {
            $("#" + entityType + "Form").trigger("click");
        }
    }
    if ($("#tabs").length) {
        $("#tabs").tabs({ heightStyle: "content" });
    }
});

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

function populateEntityLogo(entityType, entityId)
{
    "use strict";
    $("#" + entityType + "Logo").html("");
    $.get(pelagosBasePath + "/services/entity/" + entityType + "/" + entityId)
    .done(function(data) {
        $("#" + entityType + "Logo").html("<img src=\"data:" + data.data.logo.mimeType + ";base64," + data.data.logo.base64 + "\">");
    });
}

function updateEntity(entityType, entityId, jsonData)
{
    "use strict";
    var title = "";
    var message = "";
    var theurl;
    var type;
    var returnCode;
    if (entityId === null) {
        theurl = pelagosBasePath + "/services/entity/" + entityType;
        type = "POST";
        returnCode = 201;
    } else {
        theurl = pelagosBasePath + "/services/entity/" + entityType + "/" + entityId;
        type = "PUT";
        returnCode = 200;
    }

    $.ajax({
        type: type,
        data: jsonData,
        url: theurl,
        // Optionally enforce JSON return, in case a status 200 happens, but no JSON returns
        //dataType: "json"
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(json) {
        if (json.code === returnCode) {
            title = "Success!";
            message = json.message;
            $("#" + entityType + "Form").editableForm("reset");
            $("#" + entityType + "Form").fillForm(json.data);
            if ($("#" + entityType + "Logo").length) {
                $("#" + entityType + "Logo").html("<img src=\"data:" + json.data.logo.mimeType + ";base64," + json.data.logo.base64 + "\">");
            }
        } else {
            title = "Error!";
            message = "Something went wrong!<br>Didn't receive the correct success message!";
            message += "<br>Please contact <a href=\"mailto:griidc@gomri.org&subject=userland\">griidc@gomri.org</a>";
        }
    })
    .fail(function(response) {
        var json = response.responseJSON;
        if (typeof response.responseJSON === "undefined") {
            json = {};
            json.code = response.status;
            json.message = response.statusText;
        }
        title = "Error!";
        message = json.message;
    })
    .always(function(json) {
        if (json.code !== returnCode) {
            showDialog(title, message);
        } else {
            $("#notycontainer").noty({
                layout: "top",
                text: message,
                theme: "relax",
                animation: {
                    open: "animated bounceIn", // Animate.css class names
                    close: "animated fadeOut", // Animate.css class names
                    easing: "swing", // unavailable - no need
                    speed: 500 // unavailable - no need
                },
                timeout: 3000
            });
        }
    });
}
