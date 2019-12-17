var $ = jQuery.noConflict();

//FOUC preventor
$("html").hide();

$(document).ready(function () {
    "use strict";

    $("html").show();

    $(".pelagosNoty").pelagosNoty({timeout: 0, showOnTop:false});

    $("form").validate({
        submitHandler: function(form) {
            $.when(showEndReviewConfirm())
            .then(function() {
                // For the "native" submit to work, the form has to have a name.
                form.submit();
            });
        }
    });

    function showEndReviewConfirm () {
        var udi = $("#endReview_datasetUdi").val();
        var msg = $("#confirmationText").text() + " " + udi + "?";
        return showConfirmation({
            title: "please confirm",
            message: msg
        });
    }
});


