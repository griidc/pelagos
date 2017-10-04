$(document).ready(function()
{
    $(".startDate").datepicker({
        dateFormat: "yy-mm-dd",
        changeMonth: true,
        changeYear: true,
        autoSize:true,
        onClose: function(selectedDate) {
            $(".endDate").datepicker("option", "minDate", selectedDate);
        }
    });
    
    $(".endDate").datepicker({
        dateFormat: "yy-mm-dd",
        changeMonth: true,
        changeYear: true,
        autoSize:true,
        onClose: function(selectedDate) {
            $(".startDate").datepicker("option", "maxDate", selectedDate);
        }
    });
});