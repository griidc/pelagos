var $ = jQuery.noConflict();

$(document).ready(function() {
    $("#view-dataset-summary").click(function() {
        if (isValidUdi($("#udi").val())) {
            $.ajax({
                url: location.href + "/" + $("#udi").val() + "/check-exists"
            }).done(function(data) {
                if (data) {
                    $("#delete-dataset").prop("disabled", false);
                    $.ajax({
                        url: location.href + "/" + $("#udi").val() + "/view"
                    }).done(function(summary) {
                        $("#summary-display").val(summary);
                    });
                }
                else {
                    alert("No records found for this UDI!");
                }
            });
        }
        else {
            alert("Invalid UDI!");
        }
    });
    $("#download-dataset-summary").click(function() {
        if (isValidUdi($("#udi").val())) {
            $.ajax({
                url: location.href + "/" + $("#udi").val() + "/check-exists"
            }).done(function(data) {
                if (data) {
                    location.href = location.href + "/" + $("#udi").val() + "/download";
                }
                else {
                    alert("No records found for this UDI!");
                }
            });
        }
        else {
            alert("Invalid UDI!");
        }
    });
    $("#delete-dataset").click(function() {
        if (isValidUdi($("#udi").val())) {
            $.ajax({
                url: location.href + "/" + $("#udi").val() + "/check-exists"
            }).done(function(data) {
                if (data) {
                    if (confirm("Are you sure you want to delete all records for this dataset?")) {
                        $.ajax({
                            url: location.href + "/" + $("#udi").val() + "/delete"
                        }).done(function(result) {
                            $("#summary-display").val(result);
                        });
                    }
                }
                else {
                    alert("No records found for this UDI!");
                }
            });
        }
        else {
            alert("Invalid UDI!");
        }
    });
});

function isValidUdi(udi) {
    return udi.match(/^[RY][1-9]\.x\d{3}\.\d{3}:\d{4}$/);
}
