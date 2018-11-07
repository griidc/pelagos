$(document).ready(function()
{
    "use strict";

    //Disable RIS ID is not in create mode
    if ($("form[entityType=\"Person\"] #id").val() !== "") {
        $("form[entityType=\"Person\"] #id").attr("readonly",true);
    }
    
    $.ajax({
        url: $("#organization").attr("data-url"),
        dataType: "json",
        success: function(json) {
            var organizationList = json;

            $("#organization").autocomplete({
                source: organizationList
            });
        }
    });

    $.ajax({
        url: $("#position").attr("data-url"),
        dataType: "json",
        success: function(json) {
            var personList = json;

            $("#position").autocomplete({
                source: personList
            });
        }
    });
});
