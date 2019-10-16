$(document).ready(function()
{
    "use strict";

    $(".entityForm[entityType=PersonDataRepository] select[name=person]")
    .select2({
        placeholder: "[Please Select a Person]",
        allowClear: true
    });

    $(".entityForm[entityType=PersonDataRepository]").on("reset", function() {
        setTimeout(function () {
            $(".entityForm[entityType=PersonDataRepository] select[name=person]").change();
        });
    });

    $(".entityForm[entityType=PersonDataRepository] select[name=person]").on('select2:close', function () {
        $(this).valid();
    });

});
