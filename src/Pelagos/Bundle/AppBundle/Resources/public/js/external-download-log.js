var $ = jQuery.noConflict();

//FOUC preventor
$("html").hide();

$(document).ready(function () {
    "use strict";

    $("html").show();

    $(".pelagosNoty").pelagosNoty({timeout: 0, showOnTop:false});

    let userType = $("#externalDownloadLog_userType");

    checkUserType(userType);

    userType.on("change", function () {
        if (userType.val() === "0") {
            $("#externalDownloadLog_username").hide().removeAttr("required");
            $('label[for=externalDownloadLog_username]').hide();
        }
    })
});

function checkUserType(userType) {
    if (userType.val() === "0") {
        $("#externalDownloadLog_username").hide().removeAttr("required");
        $('label[for=externalDownloadLog_username]').hide();
    }
}

