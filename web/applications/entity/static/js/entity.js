var $ = jQuery.noConflict();

$(document).ready(function()
{
    "use strict";
    var isLoggedIn = JSON.parse($("div[userLoggedIn]").attr("userLoggedIn"));
    $("form.entityForm").entityForm({
        canEdit: isLoggedIn
    });
});
