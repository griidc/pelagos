var $ = jQuery.noConflict();

//FOUC preventor
$("html").hide();

$(document).ready(function () {
    "use strict";

    $("html").show();

    $(".pelagosNoty").pelagosNoty({timeout: 0, showOnTop:false});
    $("form").submit(function() { showEndReviewConfirm(); });
    //$("form").submit(function() { alert("pause"); });

    function showEndReviewConfirm () {
        var msg = $("#confirmationText").text() + " " + datasetUdi.value + "?";
        showConfirmation({
            title: "please confirm",
            message: msg
        });
    }
});


