var $ = jQuery.noConflict();
var geowizard;

//FOUC preventor
$("html").hide();

$(document).ready(function(){
    "use strict";

    $("#funderList").trigger("fundersAdded", {"disabled": false});
    $("#keywordList").trigger("keywordsAdded", {"disabled": false});

    $("#keywordList").on("change", function(event){
        $('[id^="keywords_"]').remove();
        var maxKeywordId = 0;
        $.each(($("#keywordList").val().split(',')), function(key, value) {
            if (value === "") { return; }
            var newElement = document.createElement("input");
            var keywordId = value;
            newElement.id = `keywords_${maxKeywordId}`;
            newElement.name = `keywords[${maxKeywordId}]`;
            newElement.value = keywordId;
            newElement.type = "hidden";
            $('[id="keyword-items"]').append(newElement);
            maxKeywordId++;
        })
    });

    $("#udiLoadReviewform").on("change keyup mouseout", function() {
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

    $("#pelagos-content button").button();

    jQuery.validator.addMethod("trueISODate", function(value, element) {
        var regPattern = /^([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))$/
        return this.optional(element) || ((Date.parse(value)) && regPattern.test(value));
    });

    jQuery.validator.addMethod("validURL", function(value, element) {
        return (value === "") || this.optional(element) || /^(?:(?:(?:https?|ftp):)?\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})).?)(?::\d{2,5})?(?:[/?#]\S*)?$/i.test(value);
    }, "Please enter a valid URL.");

    var remoteURL = Routing.generate("pelagos_api_dataset_submission_validate_url", { id: $("form[datasetsubmission]").attr("datasetsubmission") });

    if ($("#remotelyHostedUrl").val()) {
        $("#datasetFileTransferType").val("HTTP");
    }

    $("#remotelyHostedUrl, #filesUploaded, #largeFileUri").on("keyup change", function() {
        $(this).valid();
        // get the datasetFileTransferType from the active tab
        let datasetFileTransferType = $("#filetabs .ui-tabs-active").attr("datasetFileTransferType");
        // set the datasetFileTransferType
        $("#datasetFileTransferType").val(datasetFileTransferType);
    });

    $("#funderList").on("keyup change", function() {
        $(this).valid();
    });

    regForm.validate({
        rules: {
            temporalExtentBeginPosition: "trueISODate",
            temporalExtentEndPosition: "trueISODate",
            filesUploaded:{
                require_from_group: [1, '.files']
            },
            remotelyHostedUrl:{
                require_from_group: [1, '.files']
            },
            largeFileUri:{
                require_from_group: [1, '.files']
            },
            additionalFunders:{
                require_from_group: [1, '.funders']
            },
            funderList: {
                require_from_group: [1, '.funders']
            }
        },
        groups: {
            files: "filesUploaded remotelyHostedUrl largeFileUri",
            funders: "additionalFunders funderList",
        },
        messages: {
            temporalExtentBeginPosition: "Begin Date is not a valid ISO date",
            temporalExtentEndPosition: "End Date is not a valid ISO date",
            filesUploaded: {
                require_from_group: "Please upload a file, or add remotely hosted url, or Large file URI"
            },
            remotelyHostedUrl: {
                require_from_group: "Please upload a file, or add remotely hosted url, or Large file URI"
            },
            largeFileUri: {
                require_from_group: "Please upload a file, or add remotely hosted url, or Large file URI"
            },
            additionalFunders: {
                require_from_group: "This field is required. Please select a funder from the dropdown or add it under Additional Funders."
            },
            funderList: {
                require_from_group: "This field is required. Please select a funder from the dropdown or add it under Additional Funders."
            }
        },
        ignore: ".ignore,.prototype",
        submitHandler: function (form) {
            $("#acceptDatasetBtn, #endReviewBtn, #requestRevisionsBtn").button("disable");
            pelagosUI.loadingSpinner.showSpinner();
            form.submit();
        }
    });

    // load qTip descriptions
    $("img.info").not("#contact-prototype img.info").each(function() {
        $(this).qtip({
            content: {
                text: $(this).next(".tooltiptext").clone()
            }
        });
    });

    jQuery.validator.addClassRules('dataLinkUrl', {
        "validURL" : true,
        remote: {
            url: remoteURL,
            type: "GET",
            data: {
              erddapUrl: function() {
                return $(".dataLinkUrl", this).val();
              }
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

    var datasetLinksCount = 0;

    // Count the highest index in dataset contacts.
    $("table.dataset-links[index]").each(function() {
        var value = parseFloat($(this).attr("index"));
        datasetLinksCount = (value > datasetLinksCount) ? value : datasetLinksCount;
    });

    $("#addLink")
        .button()
        .click(function(){
            datasetLinksCount++;

            var newLink = $("#links-prototype table")
                .clone(true)
                .find(":input[id][name]")
                .removeClass("prototype error")
                .removeAttr("disabled")
                .attr("name", function() {
                    return $(this).attr("name").replace(/__name__/g, datasetLinksCount);
                })
                .attr("id", function() {
                    return $(this).attr("id").replace(/__name__/g, datasetLinksCount);
                })
                .end()
                .find("label[for]")
                .attr("for", function() {
                    return $(this).attr("for").replace(/__name__/g, datasetLinksCount);
                })
                .end()
                .fadeIn("slow");

            $("#dataset-links").append(newLink);
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
            $(this).parents("#dataset-contacts table,#dataset-links *").fadeOut("slow", function() {
                $(deleteTable).parents("#dataset-contacts table,#dataset-links *")
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
            $(ui.newTab.find("[href]").get(0).hash).trigger("active");
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

    $("#datasetFilePath").on("keyup change", function() {
        $("#largeFileUri").val($(this).val()).trigger("change");
    });

    $("#clearLargeFilePath").on("click", function () {
        $("#datasetFilePath")[0].selectedIndex = 0;
        $("#largeFileUri").val("").trigger("change");
    });

    function populateFolderDropDownList() {
        let dropdown = $('#datasetFilePath');

        dropdown.empty();

        dropdown.append('<option selected="true" value="">Choose Folder</option>');
        dropdown.prop('selectedIndex', 0);

        const url = Routing.generate("pelagos_api_get_folder_list_dataset_submission");

        // Populate dropdown with list of folders
        $.getJSON(url, function (data) {
            $.each(data, function (key, value) {
                const regex = new RegExp('(?:.(?!\/.*\/.*\/))+$');
                let text = regex.exec(value)[0];
                dropdown.append($('<option></option>').attr('value', value).text(text));
            })
        });
    }

    dtabs.on("active", function() {
        var activeTab = $("#dtabs").tabs("option","active");
        if (activeTab === 0) {
            btnPrevious.button("disable");
            btnPrevious.hide();
        } else {
            btnPrevious.show();
            btnPrevious.button("enable");
        }
        if (activeTab === 8) {
            btnNext.button("disable");
            btnNext.hide();
            populateFolderDropDownList();
        } else {
            btnNext.show();
            btnNext.button("enable");
        }
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

    $("input.keywordinput").on("keypress", function(event) {
        if (event.which == 13) {
            event.preventDefault();
            $(event.currentTarget).closest("table.keywords").find("button:contains(add)").trigger("click");
        }
    });
    buildKeywordLists();

    $("form :input").not("input.keywordinput").on('keydown', function(event) {
        if (event.which == 13) {
            return false;
        }
    });

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
                        $(".distributionurl").val(Routing.generate("pelagos_homepage") + "/data/" + $("#regForm").attr("udi"));
                    }
              }
        });
    });

    checkColdStorageCheckBoxState();
    //coldstorage checkbox - distribution info tab
    $(".coldstorage-checkbox").change(function (){
        if (!$(this).is(":checked") &&
            ($("#datasetFileColdStorageArchiveSha256Hash").val().trim() !== "" || $("#datasetFileColdStorageArchiveSize").val() !== "" || $("#datasetFileColdStorageOriginalFilename").val() !== ""  ) ) {
            $(this).prop("checked", true); //re-check because stop propagation doesn't work
            showDialog("Cold Storage Information", "Please make sure the cold storage properties are all empty before unchecking.");
        }
        else checkColdStorageCheckBoxState();
    });

    checkRemotelyHostedCheckBoxState();
    $(".remotelyhosted-checkbox").change(function (){
        if (!$(this).is(":checked") &&
            ($("#remotelyHostedName").val().trim() !== "" || $("#remotelyHostedDescription").val() !== "" || $("#remotelyHostedFunction").val() !== ""  ) ) {
            $(this).prop("checked", true); //re-check because stop propagation doesn't work
            showDialog("Is Remotely Hosted Information", "Please make sure the remotely hosted properties are all empty before unchecking.");
        }
        else checkRemotelyHostedCheckBoxState();
    });
});

function checkColdStorageCheckBoxState() {
    if ($(".coldstorage-checkbox").is(":checked")) {
        $(".row-coldstorage-filesize").show();
        $(".row-coldstorage-sha256hash").show();
        $(".row-coldstorage-original-filename").show();
        $("#datasetFileColdStorageArchiveSize").attr("required", "required");
        $("#datasetFileColdStorageArchiveSha256Hash").attr("required", "required");
        $("#datasetFileColdStorageOriginalFilename").attr("required", "required");
    } else {
        $(".row-coldstorage-filesize").hide();
        $(".row-coldstorage-sha256hash").hide();
        $(".row-coldstorage-original-filename").hide();
        $("#datasetFileColdStorageArchiveSize").removeAttr("required");
        $("#datasetFileColdStorageArchiveSha256Hash").removeAttr("required");
        $("#datasetFileColdStorageOriginalFilename").removeAttr("required");
    }
}

function checkRemotelyHostedCheckBoxState() {
    if ($(".remotelyhosted-checkbox").is(":checked")) {
        $(".row-remotely-hosted-name").show();
        $(".row-remotely-hosted-description").show();
        $(".row-remotely-hosted-function").show();
        $("#remotelyHostedName").attr("required", "required");
        $("#remotelyHostedDescription").attr("required", "required");
        $("#remotelyHostedFunction").attr("required", "required");
    } else {
        $(".row-remotely-hosted-name").hide();
        $(".row-remotely-hosted-description").hide();
        $(".row-remotely-hosted-function").hide();
        $("#remotelyHostedName").removeAttr("required");
        $("#remotelyHostedDescription").removeAttr("required");
        $("#remotelyHostedFunction").removeAttr("required");
    }
}

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
                    $(this).find(":input").not(".prototype, button").each(function () {
                        if ($(this).valid) {
                            $("#" + label).next("img").prop("src", imgCheck);
                        } else {
                            $("#" + label).next("img").prop("src", imgWarning);
                            isValid = false;
                        }
                    });
                });
            });
        });

        return isValid;
    } else {
        return false;
    }
}
