$(document).ready(function()
{
    "use strict";

    $(".entityForm[entityType=PersonFundingOrganization] select[name=person]").select2({
        placeholder: "[Please Select a Person]",
        allowClear: true
    });

});
