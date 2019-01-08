var $ = jQuery.noConflict();
var geowizard;

//FOUC preventor
$("html").hide();

$(document).ready(function(){
    "use strict";

    $("#udiLoadReviewform").bind("change keyup mouseout", function() {
        var udiTextBox = $("#udiReview");
        if($(this).valid() && udiTextBox.val() !== "" && udiTextBox.is(":disabled") === false) {
            $(".reviewButtons").button({
                disabled: false
            });
        } else {
            $(".reviewButtons").button({
                disabled: true
            });
        }
    });

    var regForm = $("#regForm");
    // Check if mode = view (View mode (Unable to edit)).
    if (regForm.attr("mode") === "view") {
        // Disable all input fields
        $("#regForm :input").prop("disabled", true);
    }

    $("html").show();

    $("label").next("input[required],textarea[required],select[required]").prev().addClass("emRequired");

    $("button").button();

    jQuery.validator.addMethod("trueISODate", function(value, element) {
        var regPattern = /^([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))$/
        return this.optional(element) || ((Date.parse(value)) && regPattern.test(value));
    });

    regForm.validate({
        rules: {
            referenceDate: "trueISODate",
            temporalExtentBeginPosition: "trueISODate",
            temporalExtentEndPosition: "trueISODate",
        },
        messages: {
            referenceDate: "It is not a valid ISO date",
            temporalExtentBeginPosition: "Begin Date is not a valid ISO date",
            temporalExtentEndPosition: "End Date is not a valid ISO date"
        },
        ignore: ".ignore,.prototype",
        submitHandler: function(form) {
            if ($(".ignore").valid()) {
                formHash = regForm.serialize();
                regForm.prop("unsavedChanges", false);
                form.submit();
            }
        }
    });

    var datasetContactsCount = 0;

    // Count the highest index in dataset contacts.
    $("table.dataset-contacts[index]").each(function() {
        var value = parseFloat($(this).attr("index"));
        datasetContactsCount = (value > datasetContactsCount) ? value : datasetContactsCount;
    });

    $("#addContact")
        .button()
        .click(function(){
            datasetContactsCount++;

            var newContact = $("#contact-prototype table")
                .clone(true)
                .find(":input[id][name]")
                .removeClass("prototype error")
                .removeAttr("disabled")
                .attr("name", function() {
                    return $(this).attr("name").replace(/__name__/g, datasetContactsCount);
                })
                .attr("id", function() {
                    return $(this).attr("id").replace(/__name__/g, datasetContactsCount);
                })
                .end()
                .find("label[for]")
                .attr("for", function() {
                    return $(this).attr("for").replace(/__name__/g, datasetContactsCount);
                })
                .end()
                .fadeIn("slow");

            $("#dataset-contacts").append(newContact);

            select2ContactPerson();

            $("img.info", newContact).each(function() {
                $(this).qtip({
                    content: {
                        text: $(this).next(".tooltiptext").clone()
                    }
                });
            });
        });

    $(".deletebutton")
        .button()
        .hover(function() {
            $(this).parents("table").addClass("delete-contact");
        }, function() {
            $(this).parents("table").removeClass("delete-contact");
        })
        .click(function(){
            var deleteTable = this;
            $(this).parents("#dataset-contacts table").fadeOut("slow", function() {
                $(deleteTable).parents("#dataset-contacts table")
                    .find(".error").remove()
                    .end()
                    .find(":input").trigger("blur")
                    .end()
                    .remove();
            });
        });

    var dtabs = $("#dtabs");
    dtabs.tabs({
        heightStyle: "content",
        activate: function(event, ui) {
            $(ui.newTab.context.hash).trigger("active");
        }
    });

    var fileTabs = $("#filetabs");

    fileTabs.tabs();

    switch ($("#datasetFileTransferType").val()) {
        case "upload":
            fileTabs.tabs("option", "active", 0);
            break;
        case "SFTP":
            fileTabs.tabs("option", "active", 1);
            break;
        case "HTTP":
            fileTabs.tabs("option", "active", 2);
            break;
    }

    var btnPrevious = $("#btn-previous");
    var btnNext = $("#btn-next");
    btnPrevious.click(function() {
        var activeTab = dtabs.tabs("option","active");
        activeTab--;
        if (activeTab < 0) {activeTab = 0};
        dtabs.tabs({active:activeTab});
    }).button("disable");

    btnNext.click(function() {
        var activeTab = dtabs.tabs("option","active");
        activeTab++;
        dtabs.tabs({active:activeTab});
    });

    dtabs.on("active", function() {
        var activeTab = $("#dtabs").tabs("option","active");
        if (activeTab === 0) {
            btnPrevious.button("disable");
            btnPrevious.hide();
        } else {
            btnPrevious.show();
            btnPrevious.button("enable");
        }
        if (activeTab === 7) {
            btnNext.button("disable");
            btnNext.hide();
        } else {
            btnNext.show();
            btnNext.button("enable");
        }
    });

    // set up DatePickers
    $("#referenceDate").datepicker({
        dateFormat: "yy-mm-dd",
        autoSize:true,
    });

    $("#temporalExtentBeginPosition").datepicker({
        dateFormat: "yy-mm-dd",
        changeMonth: true,
        changeYear: true,
        showButtonPanel: false,
        autoSize:true,
        onClose: function(selectedDate) {
            $("#temporalExtentEndPosition").datepicker("option", "minDate", selectedDate);
        }
    });

    $("#temporalExtentEndPosition").datepicker({
        dateFormat: "yy-mm-dd",
        changeMonth: true,
        changeYear: true,
        showButtonPanel: false,
        autoSize:true,
        onClose: function(selectedDate) {
            $("#temporalExtentBeginPosition").datepicker("option", "maxDate", selectedDate);
        }
    });

    $("#ds-contact,#ds-metadata-contact").on("active", function() {
        select2ContactPerson();
    });

    $("#acceptDatasetBtn, #endReviewBtn, #requestRevisionsBtn").on("click", function() {
        if (areTabsValid() === false) {
            showDialog("Missing required field(s)", "Please fill in all the required fields.");
        }
    });

    select2ContactPerson();

    $(".pelagosNoty").pelagosNoty({timeout: 0, showOnTop:false});

    geowizard = new MapWizard(
        {
            "divSmallMap":"smlMDEMap",
            "divSpatial":"spatial",
            "divNonSpatial":"nonspatial",
            "divSpatialWizard":"spatwizbtn",
            "gmlField":"spatialExtent",
            "descField":"spatialExtentDescription",
            "spatialFunction":"checkSpatial",
            "validateGeometry": true,
            "inputGmlControl": true,
        }
    );

    if ($("#spatialExtent").val() !== ""
        && (
            $("#temporalExtentDesc").val() !== ""
            || $("#temporalExtentBeginPosition").val() !== ""
            || $("#temporalExtentEndPosition").val() !== ""
        )
    ) {
        // if we have spatial and temporal extents, show spatial and temporal extent
        geowizard.haveSpatial(false);
    } else if ($("#spatialExtentDescription").val() !== "") {
        // else if we have a description, show description
        geowizard.haveSpatial(true);
    } else {
        // otherwise show spatial and temporal extent
        geowizard.haveSpatial(false);
    }

    $("#ds-extent").on("active", function() {
        geowizard.flashMap();
        geowizard.haveGML($("#spatialExtent").val());
    });

    $("select.keywordinput").dblclick(function (event) {
        var element = $(event.currentTarget)
        if (element.filter("[keyword=source]").length > 0) {
            element.closest("table.keywords").find("button:contains(add)").click();
        } else if (element.filter("[keyword=target]").length > 0) {
            element.closest("table.keywords").find("button:contains(remove)").click();
        }
    });

    $("input.keywordinput").keypress(function(event) {
        if (event.which == 13) {
            event.preventDefault();
            $(event.currentTarget).closest("table.keywords").find("button:contains(add)").click()
        }
    });
    buildKeywordLists();

    $(".keywordbutton").click(function (event) {
        var source = $(event.currentTarget).closest("table.keywords").find("input[keyword=source],select[keyword=source]");
        var target = $(event.currentTarget).closest("table.keywords").find("select[keyword=target]");

        if ($(event.currentTarget).text() === "add") {
            if (source.is("input") && source.val() !== "") {
                var optionText = source.val();
                var option = new Option(optionText, optionText);
                $(option).html(optionText);
                target.append(option);
                source.val("");
            } else if (source.is("select")) {
                var option = source.find("option:selected").detach().prop("selected", false);
                target.append(option);
                target.append(sortOptions(target.find("option").detach()));
            }
        } else if ($(event.currentTarget).text() === "remove") {
            var option = target.find("option:selected").detach().prop("selected", false);
            if (option.attr("order") != undefined) {
                source.append(option);
                source.append(sortOptions(source.find("option").detach()));
            } else {
                source.val($(option).val()).focus();
            }
        } else if ($(event.currentTarget).text() === "up") {
            var selectedOption = target.find("option:selected");
            var prevOption = selectedOption.prev("option");
            if (prevOption.is("option")) {
                selectedOption.detach().insertBefore(prevOption);
            }
        } else if ($(event.currentTarget).text() === "down") {
            var selectedOption = target.find("option:selected");
            var nextOption = selectedOption.next("option");
            if (nextOption.is("option")) {
                selectedOption.detach().insertAfter(nextOption);
            }
        }
        buildKeywordLists();
    });

    // Display admin menu when "End View" button in clicked.
    $("#EndViewBtn").click(function (event) {
        window.location = Routing.generate("pelagos_app_ui_datasetreview_default");
    });

    // Placed here as timing is too early in previous related section. Enables/Disables
    // the "End View" button depending on usage.
    if (regForm.attr("mode") === "view") {
        $("#EndViewBtn").button("enable");
    } else {
        $("#EndViewBtn").button("disable");
    }

    // Build list arrays/fake multiselect boxes.
    function buildKeywordLists()
    {
        $("#themeKeywords option").remove();
        $("#themeKeywords").append($("#theme-keywords").find("option").clone().prop("selected", true)).change();

        $("#placeKeywords option").remove();
        $("#placeKeywords").append($("#place-keywords").find("option").clone().prop("selected", true)).change();

        $("#topicKeywords option").remove();
        $("#topicKeywords").append($("#topic-keywords").find("option").clone().prop("selected", true)).change();
    }

    function sortOptions(options) {
        return options.sort(function(a,b){
            a = $(a).attr("order");
            b = $(b).attr("order");

            return a-b;
        });
    }

    function select2ContactPerson() {
        $(".contactperson").not("#contact-prototype .contactperson").select2({
            placeholder: "[Please Select a Person]",
            allowClear: true,
            ajax: {
                dataType: "json",
                data: function (params) {
                    if (params.term != undefined) {
                        var query = {
                            "lastName": params.term + "*"
                        }
                    } else {
                        var query = {}
                    }
                    return query;
                },
                url: Routing.generate("pelagos_api_people_get_collection",
                    {
                        "_properties" : "id,firstName,lastName,emailAddress",
                        "_orderBy" : "lastName,firstName,emailAddress",
                        "personResearchGroups.researchGroup" : $("[researchGroup]").attr("researchGroup"),
                    }
                ),
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.lastName + ", " +  item.firstName + ", " + item.emailAddress,
                                id: item.id
                            }
                        })
                    };
                }
            }
        });
    }

    // Direct Upload
    $("#fine-uploader").fineUploader({
        template: "qq-template",
        multiple: false,
        request: {
            endpoint: Routing.generate("pelagos_api_upload_post")
        },
        session: {
            endpoint: Routing.generate("pelagos_api_dataset_submission_get_uploaded_files", { id: $("form[datasetsubmission]").attr("datasetsubmission") })
        },
        chunking: {
            enabled: true,
            partSize: 10000000,
            concurrent: {
                enabled: true
            },
            success: {
                endpoint: Routing.generate("pelagos_api_upload_post") + "?done"
            }
        },
        resume: {
            enabled: true
        },
        retry: {
            enableAuto: true
        },
        deleteFile: {
            enabled: $(".submitButton").attr("disabled") !== "disabled",
            forceConfirm: true,
            endpoint: Routing.generate("pelagos_api_upload_delete")
        },
        callbacks: {
            onSessionRequestComplete: function (response, success, xhrOrXdr) {
                if (response.length > 0) {
                    $("#fine-uploader .qq-upload-button").hide();
                }
            },
            onSubmit: function (id, name) {
                setDatasetFileUri("");
                $("#fine-uploader .qq-upload-button").hide();
            },
            onProgress: function (id, name, totalUploadedBytes, totalBytes) {
                updateSpeedText(totalUploadedBytes, totalBytes);
            },
            onComplete: function (id, name, responseJSON, xhr) {
                if (responseJSON.success) {
                    setDatasetFileUri(responseJSON.path);
                    areTabsValid();
                }
            },
            onDelete: function (id) {
                setDatasetFileUri("");
            },
            onStatusChange: function (id, oldStatus, newStatus) {
                switch (newStatus) {
                    case qq.status.CANCELED:
                    case qq.status.DELETED:
                    case qq.status.PAUSED:
                    case qq.status.UPLOAD_SUCCESSFUL:
                        resetSpeedText();
                        areTabsValid();
                }
                switch (newStatus) {
                    case qq.status.CANCELED:
                    case qq.status.DELETED:
                        $("#fine-uploader .qq-upload-button").show();
                }
            }
        }
    });

    // Request SFTP/GridFTP button
    $("#sftpButton").click(function() {
        $("#spinner").show();
        $.ajax({
            url: $("#sftpButton").attr("sftppath"),
            type: "PATCH",
            success: function() {
                showDialog("SFTP Access", "SFTP Access has been granted.");
                $(".sftpYes").show();
                $(".sftpNo").hide();
                // Enable file browse buttons..
                $(".fileBrowserButton").prop("disabled", false);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                showDialog("Problem with your request", jqXHR.responseJSON.message);
            }
        }).always(function() {
            $("#spinner").hide();
        });
    });
    // File browser for SFTP/GridFTP
    $(".fileBrowserButton").fileBrowser({
        url: Routing.generate("pelagos_api_account_get_incoming_directory", { id: "self" })
    });

    // SFTP/GridFTP and HTTP/FTP
    $("#datasetFilePath, #datasetFileUrl").on("keyup change", function() {
        $(this).valid();
        setDatasetFileUri($(this).val());
    });

    // set the datasetFileUri and datasetFileTransferType
    function setDatasetFileUri(datasetFileUri) {
        // get the datasetFileTransferType from the active tab
        var datasetFileTransferType = $("#filetabs .ui-tabs-active").attr("datasetFileTransferType");
        // set the datasetFileTransferType
        $("#datasetFileTransferType").val(datasetFileTransferType);
        if (datasetFileTransferType !== "upload") {
            // clear uploaded files list (Direct Upload tab)
            $(".qq-upload-list").html("")
            // show upload button (Direct Upload tab)
            $("#fine-uploader .qq-upload-button").show();
        }
        if (datasetFileTransferType != "SFTP") {
            // clear datasetFilePath (Upload via SFTP/GridFTP tab)
            $("#datasetFilePath").val("");
        }
        if (datasetFileTransferType != "HTTP") {
            // clear datasetFileUrl (Request Pull from HTTP/FTP Server tab)
            $("#datasetFileUrl").val("");
            // if datasetFileUri is set
            if (datasetFileUri != "") {
                // prepend file uri prefix
                datasetFileUri = "file://" + datasetFileUri;
            }
        }
        // set datasetFileUri
        $("#datasetFileUri").val(datasetFileUri);
        $("#datasetFileUri").valid();

    }

    var uploadSpeeds = [];
    var updateSpeeds = true;

    function updateSpeedText(totalUploadedBytes, totalBytes) {
        if (!updateSpeeds) {
            return;
        }
        uploadSpeeds.push({
            totalUploadedBytes: totalUploadedBytes,
            currentTime: new Date().getTime()
        });
        var minSamples = 6;
        var maxSamples = 20;
        if (uploadSpeeds.length > maxSamples) {
            uploadSpeeds.shift();
        }
        if (uploadSpeeds.length >= minSamples) {
            try {
                var firstSample = uploadSpeeds[0];
                var lastSample = uploadSpeeds[uploadSpeeds.length - 1];
                var progressBytes = lastSample.totalUploadedBytes - firstSample.totalUploadedBytes;
                var progressTimeMS = lastSample.currentTime - firstSample.currentTime;
                var bytesPerSecond = progressBytes / (progressTimeMS / 1000);
                if (bytesPerSecond > 0) {
                    var speedPrecision = 0;
                    var MBps = bytesPerSecond / 1e6;
                    if (MBps < 10) {
                        speedPrecision = 1;
                    }
                    if (MBps < 1) {
                        speedPrecision = 2;
                    }
                    if (MBps < 0.1) {
                        speedPrecision = 3;
                    }
                    $("#uploader-speed").text("Transfer speed: " + MBps.toFixed(speedPrecision) + " MB per second");
                    var remainingDays = 0;
                    var remainingHours = 0;
                    var remainingMinutes = 0;
                    var remainingSeconds = ((totalBytes - totalUploadedBytes) / bytesPerSecond).toFixed(0);
                    if (remainingSeconds >= 60) {
                        remainingMinutes = Math.floor(remainingSeconds / 60);
                        remainingSeconds %= 60;
                    }
                    if (remainingMinutes >= 60) {
                        remainingHours = Math.floor(remainingMinutes / 60);
                        remainingMinutes %= 60;
                    }
                    if (remainingHours >= 24) {
                        remainingDays = Math.floor(remainingHours / 24);
                        remainingHours %= 24;
                    }
                    var remainingText = "";
                    if (remainingDays > 0) {
                        remainingText += " " + remainingDays + " day";
                        if (remainingDays > 1) {
                            remainingText += "s";
                        }
                    }
                    if (remainingHours > 0) {
                        remainingText += " " + remainingHours + " hour";
                        if (remainingHours > 1) {
                            remainingText += "s";
                        }
                    }
                    if (remainingMinutes > 0) {
                        remainingText += " " + remainingMinutes + " minute";
                        if (remainingMinutes > 1) {
                            remainingText += "s";
                        }
                    }
                    if (remainingSeconds > 0) {
                        remainingText += " " + remainingSeconds + " second";
                        if (remainingSeconds > 1) {
                            remainingText += "s";
                        }
                    }
                    $("#uploader-remaining").text("Time remaining:" + remainingText);
                    updateSpeeds = false;
                    setTimeout(function () {
                        updateSpeeds = true;
                    }, 500);
                }
            } catch (err) {
            }
        }
    }

    function resetSpeedText() {
        $("#uploader-speed").text("");
        $("#uploader-remaining").text("");
        uploadSpeeds = [];
        updateSpeeds = true;
    }

    $("#temporalInfoQuestion").on("change", function (e) {
        checkTemporalNilReason();
    });

    // Check if mode = view (The if loop here is duplicated at the end because spatialWizard and fineUploader
    // need to be disabled after they are initialized).
    if (regForm.attr("mode") === "view") {
        // Disable fineupload Drag and Drop area.
        $(".qq-upload-drop-area").css("visibility", "hidden");
        // Disable the upload buttons
        $(".qq-upload-button :input").prop("disabled", true);
        // Disable Spatial Wizard button.
        $("#geoWizard #geowizBtn").prop("disabled", "true");
    }

    //change info in distribution contact information according to the selected value from drop-down
    $("#distributioncontact").change(function() {
        $.ajax({
              url: Routing.generate("pelagos_api_data_center_get", { "id" : $("#distributioncontact :selected").val() }),
              success: function(data){
                    $("#distcontact_address").text(data.deliveryPoint ? data.deliveryPoint : "");
                    $("#distcontact_city").text(data.city ? data.city : "");
                    $("#distcontact_state").text(data.administrativeArea ? data.administrativeArea : "");
                    $("#distcontact_postalcode").text(data.postalCode ? data.postalCode : "");
                    $("#distcontact_country").text(data.country ? data.country : "");
                    $("#distcontact_phonenumber").text(data.phoneNumber ? data.phoneNumber : "");
                    $("#distcontact_emailaddress").text(data.emailAddress ? data.emailAddress : "");
                    $("#distcontact_url").text(data.organizationUrl ? data.organizationUrl : "");

                    //auto-generate/clear distribution fields
                    if ("GRIIDC" === data.organizationName) {
                        $(".distributionurl").val("https://data.gulfresearchinitiative.org/data/" + $("#regForm").attr("udi"));
                    }
              }
        });
    });

    var erddapUrl = $("#erddapUrl");

    erddapUrl.change(function () {
        $.ajax({
            url: Routing.generate("pelagos_api_dataset_submission_validate_url", { id: $("form[datasetsubmission]").attr("datasetsubmission") }),
            type: "PUT",
            data: {"url": erddapUrl.val()},
            success: function () {
                $(this).valid();
                erddapUrl
                    .removeClass("error");
            },
            error: function () {
                erddapUrl
                    .addClass("error")
                    .removeClass("valid")
                    .after("<label for=\"erddapUrl\" class=\"error\">Please provide a valid URL.</label>");
            }
        });
    });
});

function checkSpatial(isNonSpatial) {
    if (isNonSpatial) {
        $("#nonspatial").find(":input").attr("required", "required");
        $("#spatial").find(":input").removeAttr("required");
        $(".spatialExtras").hide().find(":input").removeAttr("required").val("");
        $(".nilReasonTemporal").hide().find(":input").removeAttr("required").val("");
        $("#temporalInfoQuestion").hide();

    } else {
        $("#spatial").find(":input").attr("required", "required");
        $("#nonspatial").find(":input").removeAttr("required");
        $("#temporalInfoQuestion").show();
        $(".spatialExtras").show().find(":input").attr("required", "required");
        checkTemporalNilReason();
    }
}

function checkTemporalNilReason() {
    if ($("#checkNilReason").prop("checked")) {
        $(".nilReasonTemporal").hide().find(":input").removeAttr("required").val("");
        $(".spatialExtras").show().find(":input").attr("required", "required");
    } else{
        $(".spatialExtras").hide().find(":input").removeAttr("required").val("");
        $(".nilReasonTemporal").show().find(":input").attr("required", "required");
    }
}

function areTabsValid()
{
    var regForm = $("#regForm");
    if (regForm.attr("mode") === "review") {
        $("#regForm select[keyword=target] option").prop("selected", true);
        var imgWarning = $("#imgwarning").attr("src");
        var imgCheck = $("#imgcheck").attr("src");
        var isValid = regForm.valid();
        $(".tabimg").show();

        $("#dtabs .ds-metadata").each(function () {
            var tabLabel = $(this).attr("aria-labelledby");
            if ($(this).has(":input.error").not("button").length > 0) {
                $("#" + tabLabel).next("img").prop("src", imgWarning);
                isValid = false;
            }
            else {
                $("#" + tabLabel).next("img").prop("src", imgCheck);
            }

            $(this).find(":input").on("change blur keyup", function () {
                $("#dtabs .ds-metadata").each(function () {
                    var label = $(this).attr("aria-labelledby");
                    $(this).find(":input").not(".prototype, button").each(function () {
                        $(this).valid()
                    });
                    if ($(this).find(":input").not(".prototype, button").valid()) {
                        $("#" + label).next("img").prop("src", imgCheck);
                    } else {
                        $("#" + label).next("img").prop("src", imgWarning);
                        isValid = false;
                    }
                });
            });
        });

        if (typeof $("#datasetFileUri").val() !== "undefined") {
            if ($("#datasetFileUri").val() === "") {
                $("#filetabimg").prop("src", imgWarning);
                isValid = false;
            } else {
                $("#filetabimg").prop("src", imgCheck);
            }
        }
        return isValid;
    } else {
        return false;
    }
}
