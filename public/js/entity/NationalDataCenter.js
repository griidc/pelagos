var $ = jQuery.noConflict();

$(document).ready(function () {
   "use strict";

   var phoneNumber = $("#phoneNumber");

    phoneNumber.val($('form[entityType="NationalDataCenter"] input[name="phoneNumber"]').val());
    phoneNumber.mask("(999) 999-9999");
    phoneNumber.prop("defaultValue", phoneNumber.val());

    var nationalDataCenterFormType = $("form[entityType=\"NationalDataCenter\"]");

    nationalDataCenterFormType.on("presubmit", function() {
        var phoneValue = phoneNumber.val().replace(/[^\d]/g, "");
        $('form[entityType="NationalDataCenter"] input[name="phoneNumber"]').val(phoneValue);
    });

    nationalDataCenterFormType.on("reset", function() {
        setTimeout(function() {
            var value = $('form[entityType="NationalDataCenter"] input[name="phoneNumber"]').val();
            phoneNumber.val(value);
            phoneNumber.mask("(999) 999-9999");
            phoneNumber.prop("defaultValue", phoneNumber.val());
        });
    });
});