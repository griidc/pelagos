var $ = jQuery.noConflict();

$(document).ready(function() {
    var datasetSubHistory = [];
    var datasetSubmissionId = "";
    var deleteDataset = $("#delete-dataset");
    $("#view-dataset-summary").click(function() {
        var properties = [
            "creator",
            "modifier",
            "researchGroup",
            "dif",
            "dif.creator",
            "dif.modifier",
            "datasetSubmission",
            "datasetSubmission.creator",
            "datasetSubmission.modifier",
            "datasetSubmissionHistory",
            "datasetSubmissionHistory.creator",
            "datasetSubmissionHistory.modifier",
            "datasetPublications",
            "datasetPublications.creator",
            "datasetPublications.modifier",
            "datasetPublications.publication"
        ];
        $.ajax({
            url: Routing.generate("pelagos_api_datasets_get_collection"),
            data: {
                udi: $("#udi").val(),
                _properties: properties.join(",")
            }
        }).done(function(data, textStatus, jqXHR) {
            if (data.length == 0) {
                $("#summary-display").val("Not found!");
            } else {
                $("#summary-display").val(JSON.stringify(data, undefined, 4));
                $("#download-dataset-summary").prop("disabled", false);
                deleteDataset.attr("datasetId", data[0].id);
                datasetSubHistory = data[0].datasetSubmissionHistory;
                datasetSubmissionId = data[0].datasetSubmission.id;
            }
        });
    });
    $("#download-dataset-summary").click(function() {
        saveTextAsFile($("#summary-display").val(),  $("#udi").val() + ".json")
        deleteDataset.prop("disabled", false);
    });
    deleteDataset.click(function() {
        if (confirm("Are you sure you want to delete all records for this dataset?")) {
            $.ajax({
                url: Routing.generate("pelagos_api_datasets_get",{ "id" : deleteDataset.attr("datasetId") }),
                method: "DELETE",
                success: function() {
                    $("#summary-display").val("Dataset deleted!");
                },
                error: function(jqXHR, textStatus) {
                    if (jqXHR.responseJSON == undefined) {
                        $("#summary-display").val(jqXHR.statusText);
                    } else {
                        $("#summary-display").val(JSON.stringify(jqXHR.responseJSON, undefined, 4));
                    }
                }
            });
        }
    });
    $("#udi").on("input", function() {
        deleteDataset.prop("disabled", true);
        $("#download-dataset-summary").prop("disabled", true);
        $("#summary-display").val("");
    });
});

function saveTextAsFile(textToWrite, fileNameToSaveAs)
{
    var textFileAsBlob = new Blob([textToWrite], {type:'text/plain'});

    var downloadLink = document.createElement("a");
    downloadLink.download = fileNameToSaveAs;
    downloadLink.innerHTML = "Download File";

    if (window.webkitURL != null)
    {
        // Chrome allows the link to be clicked
        // without actually adding it to the DOM.
        downloadLink.href = window.webkitURL.createObjectURL(textFileAsBlob);
    }
    else
    {
        // Firefox requires the link to be added to the DOM
        // before it can be clicked.
        downloadLink.href = window.URL.createObjectURL(textFileAsBlob);
        downloadLink.onclick = destroyClickedElement;
        downloadLink.style.display = "none";
        document.body.appendChild(downloadLink);
    }
    downloadLink.click();
}

function destroyClickedElement(event)
{
    document.body.removeChild(event.target);
}
