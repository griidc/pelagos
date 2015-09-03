var $ = jQuery.noConflict();

$(document).ready(function()
{
    "use strict";

    var isLoggedIn = JSON.parse($("div[userLoggedIn]").attr("userLoggedIn"));
    if (isLoggedIn) {
        $("form").editableForm();
    }

    $("#fundingOrganizationForm [name=\"logo\"]").on("logoChanged", function ()
    {
        $("#fundingOrganizationLogo").html("<img src=\"data:" + $(this).attr("mimeType") + ";base64," + $(this).attr("base64") + "\">");
    });

    $("#startDate").datepicker({
        dateFormat: "yy-mm-dd",
        onClose: function(selectedDate) {
            $("#endDate").datepicker("option", "minDate", selectedDate);
            try {
                var tomorrow = new Date(selectedDate);
                var newdate = tomorrow.setDate(tomorrow.getDate() + 1);
                newdate = tomorrow.toISOString().substring(0, 10);
                $("#endDate").datepicker("option", "minDate", newdate);
            }
            catch (e) {
                /* do nothing
                 * catches if a bad date is entered
                 * */
            }

            $("#startDate").keyup();
        }
    });
    $("#endDate").datepicker({
        dateFormat: "yy-mm-dd",
        onClose: function(selectedDate) {
            try {
                var yesterday = new Date(selectedDate);
                var newdate = yesterday.setDate(yesterday.getDate() - 1);
                newdate = yesterday.toISOString().substring(0, 10);
                $("#startDate").datepicker("option", "maxDate", newdate);
            }
            catch (e) {
                /* do nothing
                 * catches if a bad date is entered
                 * */
            }
            $("#endDate").keyup();
        }
    });

    $("form").has("#startDate,#endDate").bind("reset", function() {
        $("#startDate").datepicker("option", "maxDate", "");
        $("#endDate").datepicker("option", "minDate", "");
    });
});


