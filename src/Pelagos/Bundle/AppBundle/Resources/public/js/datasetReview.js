var $ = jQuery.noConflict();
$(document).ready(function(){
    "use strict";

    $("#udiLoadReviewform").bind("change keyup mouseout", function() {
        var udiTextBox = $("#udiReview");
        if($(this).valid() && udiTextBox.val() !== "" && udiTextBox.is(":disabled") === false) {
            $("#loadReviewButton").button({
                disabled: false
            });
        } else {
            $("#loadReviewButton").button({
                disabled: true
            });
        }
    });
});