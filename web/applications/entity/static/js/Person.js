$(document).ready(function()
{
    "use strict";

    $.ajax({
        url: pelagosBasePath + "/services/entity/Person/getDistinctVals/organization",
        dataType: "json",
        success: function(json) {
            var organizationList = json.data;

            $("#organization").autocomplete({
                source: organizationList
            });
        }
    });

    $.ajax({
        url: pelagosBasePath + "/services/entity/Person/getDistinctVals/position",
        dataType: "json",
        success: function(json) {
            var personList = json.data;

            $("#position").autocomplete({
                source: personList
            });
        }
    });
});
