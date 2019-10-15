$(document).ready(function()
{
    "use strict";

    $(".entityForm[entityType=PersonFundingOrganization] select[name=person]").select2({
        placeholder: "[Please Select a Person]",
        allowClear: true
    });

    $("form[entityType=\"PersonFundingOrganization\"]").on("reset", function(event) {
        var form = event.target;

        $("#person", form).select2("val", $("#person").attr("person"));
    });

});
