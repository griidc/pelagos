var $ = jQuery.noConflict();

$(document).ready(function() {
    $("#view-dataset-summary").click(function() {
        if (isValidUdi($("#udi").val())) { 
            window.open(location.href + "/" + $("#udi").val());
        }
        else {
            alert("Invalid UDI!");
        }
    });
    $("#download-dataset-summary").click(function() {
        if (isValidUdi($("#udi").val())) { 
            window.open(location.href + "/" + $("#udi").val() + "/download");
        }
        else {
            alert("Invalid UDI!");
        }
    });
});

function isValidUdi(udi) {
    return udi.match(/^[RY][1-9]\.x\d{3}\.\d{3}:\d{4}$/);
}
