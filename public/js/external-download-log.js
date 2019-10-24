var $ = jQuery.noConflict();

$(document).ready(function () {
    "use strict";

    $(".pelagosNoty").pelagosNoty({timeout: 0, showOnTop:false});

    let userType = $("#externalDownloadLog_userType");

    checkUserType(userType);

    userType.on("change", function () {
        checkUserType(userType);
    })
});

function checkUserType(userType) {
    let username = $("#externalDownloadLog_username");
    let labelUsername = $("label[for=externalDownloadLog_username]");
    if (userType.val() === "0") {
        username.hide().removeAttr("required");
        labelUsername.hide();
    } else {
        username.show().attr("required");
        labelUsername.show();
    }
}

