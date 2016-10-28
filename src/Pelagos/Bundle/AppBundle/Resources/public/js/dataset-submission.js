var $ = jQuery.noConflict();

var geowizard;

//FOUC preventor
$("html").hide();

$(function() {
    $("html").show();

    $("label").next("input[required],textarea[required],select[required]").prev().addClass("emRequired");

    $("#regbutton").button({
        disabled: true
    });

    $("#regidform").bind("change keyup mouseout", function() {
        if($(this).validate().checkForm() && $("#regid").val() != "" && $("#regid").is(":disabled") == false) {
            $("#regbutton").button("enable");
        } else {
            $("#regbutton").button("disable");
        }
    });

    $("#regForm").validate(
        {
            ignore: ""
        }
    );

    $("#dtabs").tabs({
        heightStyle: "content",
        activate: function(event, ui) {
            $(ui.newTab.context.hash).trigger("active");
        }
    });

    $("#filetabs").tabs();

    switch ($("#datasetFileTransferType").val()) {
        case "upload":
            $("#filetabs").tabs("option", "active", 0);
            break;
        case "SFTP":
            $("#filetabs").tabs("option", "active", 1);
            break;
        case "HTTP":
            $("#filetabs").tabs("option", "active", 2);
            break;
    }

    $("button").button();

    $("#btn-previous").click(function() {
       var activeTab = $("#dtabs").tabs("option","active");
       activeTab--;
       if (activeTab < 0) {activeTab = 0};
       $("#dtabs").tabs({active:activeTab});
    });

    $("#btn-next").click(function() {
        var activeTab = $("#dtabs").tabs("option","active");
        activeTab++;
        $("#dtabs").tabs({active:activeTab});
        saveDatasetSubmission();
    });

    $("#btn-save").click(function() {
        saveDatasetSubmission();
    });

    function saveDatasetSubmission()
    {
        var datasetSubmissionId = $("form[datasetsubmission]").attr("datasetsubmission");
        var url = Routing.generate('pelagos_api_dataset_submission_patch');

        $.ajax({
            url: url + "/" + datasetSubmissionId + "?validate=false",
            method: "PATCH",
            data: $("form[datasetsubmission]").serialize(),
            success: function(data, textStatus, jqXHR) {
                var n = noty(
                {
                    layout: 'top',
                    theme: 'relax',
                    type: 'success',
                    text: 'Succesfully Saved',
                    timeout: 1000,
                    modal: false,
                    animation: {
                        open: "animated bounceIn", // Animate.css class names
                        close: "animated fadeOut", // Animate.css class names
                        easing: "swing", // unavailable - no need
                        speed: 500 // unavailable - no need
                    }
                });
            },
            error: function(jqXHR, textStatus, errorThrown) {
                var message = jqXHR.responseJSON == null ? errorThrown: jqXHR.responseJSON.message;
                var n = noty(
                {
                    layout: 'top',
                    theme: 'relax',
                    type: 'error',
                    text: message,
                    modal: true,
                });
            }
        });

    }

    $("[placeholder=yyyy-mm-dd]").datepicker({
        dateFormat: "yy-mm-dd",
        autoSize:true
    });

    $("#ds-contact,#ds-metadata-contact").on("active", function() {
        select2ContactPerson();
    });

    $("#ds-submit").on("active", function() {
        $(".invaliddsform").show();
        $(".validdsform").hide();
        $("#regForm select[keyword=target] option").prop("selected", true);
        var imgWarning = $("#imgwarning").attr("src");
        var imgCheck = $("#imgcheck").attr("src");
        var valid = $(".ds-metadata :input").valid();

        if (false == valid) {
            $(".tabimg").show();
            $("#dtabs .ds-metadata").each(function() {
                var tabLabel = $(this).attr("aria-labelledby");
                if ($(this).has(":input.error").length ? true : false) {
                    $("#" + tabLabel).next("img").prop("src", imgWarning);
                } else {
                    $("#" + tabLabel).next("img").prop("src", imgCheck);
                };

                $(this).find(":input").on("change blur keyup", function() {
                    $("#dtabs .ds-metadata").each(function() {
                        var label = $(this).attr("aria-labelledby");
                        $(this).find(":input").each(function() {
                            $(this).valid()
                        });
                        if ($(this).find(":input").valid()) {
                            $("#" + label).next("img").prop("src", imgCheck);
                        } else {
                            $("#" + label).next("img").prop("src", imgWarning);
                        };
                    });
                });
            });
        } else {
            $(".invaliddsform").hide();
            $(".validdsform").show();
        }
    });

    select2ContactPerson();
    buildKeywordLists();

    // Direct Upload
    $("#fine-uploader-manual-trigger").fineUploader({
        template: "qq-template-manual-trigger",
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
            enabled: true,
            forceConfirm: true,
            endpoint: Routing.generate("pelagos_api_upload_delete")
        },
        callbacks: {
            onSubmit: function (id, name) {
                setDatasetFileUri("");
            },
            onProgress: function (id, name, totalUploadedBytes, totalBytes) {
                updateSpeedText(totalUploadedBytes, totalBytes);
            },
            onComplete: function (id, name, responseJSON, xhr) {
                if (responseJSON.success) {
                    setDatasetFileUri(responseJSON.path);
                    saveDatasetSubmission();
                }
            },
            onDelete: function (id) {
                setDatasetFileUri("");
                saveDatasetSubmission();
            },
            onStatusChange: function (id, oldStatus, newStatus) {
                switch (newStatus) {
                    case qq.status.CANCELED:
                    case qq.status.DELETED:
                    case qq.status.PAUSED:
                    case qq.status.UPLOAD_SUCCESSFUL:
                        resetSpeedText();
                }
            }
        }
    });

    // File browser for SFTP/GridFTP
    $(".fileBrowserButton").fileBrowser({
        url: Routing.generate("pelagos_api_account_get_incoming_directory", { id: "self" })
    });

    // SFTP/GridFTP and HTTP/FTP
    $("#datasetFilePath, #datasetFileUrl").on("keyup change", function() {
        setDatasetFileUri($(this).val());
    });
    $("#datasetFilePath, #datasetFileUrl").change(function() {
        saveDatasetSubmission();
    });

    // set the datasetFileUri and datasetFileTransferType
    function setDatasetFileUri(datasetFileUri) {
        // get the datasetFileTransferType from the active tab
        datasetFileTransferType = $("#filetabs .ui-tabs-active").attr("datasetFileTransferType");
        // set the datasetFileTransferType
        $("#datasetFileTransferType").val(datasetFileTransferType);
        if (datasetFileTransferType != "upload") {
            // clear uploaded files list (Direct Upload tab)
            $(".qq-upload-list").html("")
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
        // remove datasetFileUri error label
        $('label.error[for="datasetFileUri"]').remove();
        // set datasetFileUri
        $("#datasetFileUri").val(datasetFileUri);
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
                console.log(uploadSpeeds.length);
                if (bytesPerSecond > 0) {
                    var speedPrecision = 0;
                    MBps = bytesPerSecond / 1e6;
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

    function select2ContactPerson() {
        $(".contactperson").select2({
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
                    "_orderBy" : "lastName,firstName,emailAddress"
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


    geowizard = new MapWizard(
        {
            "divSmallMap":"smlMDEMap",
            "divSpatial":"spatial",
            "divNonSpatial":"nonspatial",
            "divSpatialWizard":"spatwizbtn",
            "gmlField":"spatialExtent",
            "descField":"spatialExtentDescription",
            "spatialFunction":"checkSpatial"
        }
    );

    if ($("#spatialExtentDescription").val()!="" && $("#spatialExtent").val()=="") {
        geowizard.haveSpatial(true);
    } else {
        geowizard.haveSpatial(false);
    }

    if ($("#spatialExtent").val()!="") {
        geowizard.haveSpatial(false);
    }

    $("#ds-extent").on("active", function() {
        geowizard.flashMap();
        geowizard.haveGML($("#spatialExtent").val());
    });

    $("select.keywordinput").dblclick(function (event) {
        var element = $(event.currentTarget)
        if (element.filter("[keyword=source]").length > 0) {
            element.closest("table").find("button:contains(add)").click();
        } else if (element.filter("[keyword=target]").length > 0) {
            element.closest("table").find("button:contains(remove)").click();
        }
    });

    $("input.keywordinput").keypress(function(event) {
        if (event.which == 13) {
            event.preventDefault();
            $(event.currentTarget).closest("table").find("button:contains(add)").click()
        }
    });

    $(".keywordbutton").click(function (event) {
        var source = $(event.currentTarget).closest("table").find("input[keyword=source],select[keyword=source]");
        var target = $(event.currentTarget).closest("table").find("select[keyword=target]");

        if ($(event.currentTarget).text() == "add") {
            if (source.is("input")) {
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
        } else if ($(event.currentTarget).text() == "remove") {
            var option = target.find("option:selected").detach().prop("selected", false);
            if (option.attr("order") != undefined) {
                source.append(option);
                source.append(sortOptions(source.find("option").detach()));
            }
        }
        buildKeywordLists();
    });

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

    $.fn.qtip.defaults = $.extend(true, {}, $.fn.qtip.defaults, {
        style: {
            classes: "qtip-tipped",
        }
    });

    $("option[value=oceans]").qtip({
        content: {
            text: "<b>Oceans:</b><br/>Features and characteristics of salt water bodies (excluding inland waters) Examples: tides, tidal waves, coastal information, reefs."
        },
        position: {
            target: "mouse",
            adjust: {
                x: 30
            }
        }
    });


	$("option[value=biota]").qtip({
        content: {
            text: "<b>Biota:</b><br/>Flora and/or fauna in natural environment Examples: wildlife, vegetation, biological sciences, ecology, wilderness, sealife, wetlands, habitat."
        },
        position: {
            target: "mouse",
            adjust: {
                x: 30
            }
        }
    });

	$("option[value=boundaries]").qtip({
        content: {
            text: "<b>Boundaries:</b><br/>Legal land descriptions,  Examples: political and administrative boundaries."
        },
        position: {
            target: "mouse",
            adjust: {
                x: 30
            }
        }
    });


	$("option[value=climatologyMeteorologyAtmosphere]").qtip({
        content: {
            text: "<b>Climatology/Meteorology/Atmosphere:</b><br/>Processes and phenomena of the atmosphere Examples: cloud cover, weather, climate, atmospheric conditions, climate change, precipitation."
        },
        position: {
            target: "mouse",
            adjust: {
                x: 30
            }
        }
    });


	$("option[value=economy]").qtip({
        content: {
            text: "<b>Economy:</b><br/>Economic activities, conditions and employment Examples: production, labour, revenue, commerce, industry, tourism and ecotourism, forestry, fisheries, commercial or subsistence hunting, exploration and exploitation of resources such as minerals, oil and gas."
        },
        position: {
            target: "mouse",
            adjust: {
                x: 30
            }
        }
    });


	$("option[value=elevation]").qtip({
        content: {
            text: "<b>Elevation:</b><br/>Height above or below sea level Examples: altitude, bathymetry, digital elevation models, slope, derived products."
        },
        position: {
            target: "mouse",
            adjust: {
                x: 30
            }
        }
    });


	$("option[value=environment]").qtip({
        content: {
            text: "<b>Environment:</b><br/>Environmental resources, protection and conservation Examples: environmental pollution, waste storage and treatment, environmental impact assessment, monitoring environmental risk, nature reserves, landscape."
        },
        position: {
            target: "mouse",
            adjust: {
                x: 30
            }
        }
    });


	$("option[value=farming]").qtip({
        content: {
            text: "<b>Farming:</b><br/>Rearing of animals and/or cultivation of plants Examples: agriculture, irrigation, aquaculture, plantations, herding, pests and diseases affecting crops nd livestock."
        },
        position: {
            target: "mouse",
            adjust: {
                x: 30
            }
        }
    });


	$("option[value=geoscientificInformation]").qtip({
        content: {
            text: "<b>Geoscientific Information:</b><br/>Information pertaining to earth sciencesExamples: geophysical features and processes, geology, minerals, sciences dealing with the composition, structure and origin of the earth’s rocks, risks of earthquakes, volcanic activity, landslides, gravity information, soils, permafrost, hydrogeology, erosion."
        },
        position: {
            target: "mouse",
            adjust: {
                x: 30
            }
        }
    });

	$("option[value=health]").qtip({
        content: {
            text: "<b>Health:</b><br/>Health, health services, human ecology, and safety Examples: disease and illness, factors affecting health, hygiene, substance abuse, mental and hysical health, health services."
        },
        position: {
            target: "mouse",
            adjust: {
                x: 30
            }
        }
    });


	$("option[value=imageryBaseMapsEarthCover]").qtip({
        content: {
            text: "<b>Imagery/Base Maps/Earth Cover:</b><br/>Base maps Examples: land cover, topographic maps, imagery, unclassified images, annotations."
        },
        position: {
            target: "mouse",
            adjust: {
                x: 30
            }
        }
    });


	$("option[value=inlandWaters]").qtip({
        content: {
            text: "<b>Inland Waters:</b><br/>Inland water features, drainage systems and their characteristics Examples: rivers and glaciers, salt lakes, water utilization plans, dams, currents, loods, water quality, hydrographic charts."
        },
        position: {
            target: "mouse",
            adjust: {
                x: 30
            }
        }
    });


	$("option[value=location]").qtip({
        content: {
            text: "<b>Location:</b><br/>Positional information and servicesExamples: addresses, geodetic networks, control points, postal zones and services, place names."
        },
        position: {
            target: "mouse",
            adjust: {
                x: 30
            }
        }
    });


	$("option[value=intelligenceMilitary]").qtip({
        content: {
            text: "<b>Military Intelligence:</b><br/>Military bases, structures, activities Examples: barracks, training grounds, military transportation, information collection."
        },
        position: {
            target: "mouse",
            adjust: {
                x: 30
            }
        }
    });


	$("option[value=planningCadastre]").qtip({
        content: {
            text: "<b>Planning/Cadastre:</b><br/>Information used for appropriate actions for future use of the landExamples: land use maps, zoning maps, cadastral surveys, land ownership."
        },
        position: {
            target: "mouse",
            adjust: {
                x: 30
            }
        }
    });


	$("option[value=society]").qtip({
        content: {
            text: "<b>Society:</b><br/>Characteristics of society and cultures Examples: settlements, anthropology, archaeology, education, traditional beliefs, manners and customs, demographic data, recreational areas and activities, social impact assessments, crime and justice, census information activities, social impact assessments, crime and justice."
        },
        position: {
            target: "mouse",
            adjust: {
                x: 30
            }
        }
    });


	$("option[value=structure]").qtip({
        content: {
            text: "<b>Structure:</b><br/>Man-made construction Examples: buildings, museums, churches, factories, housing, monuments, shops, towers."
        },
        position: {
            target: "mouse",
            adjust: {
                x: 30
            }
        }
    });


	$("option[value=transportation]").qtip({
        content: {
            text: "<b>Transportation:</b><br/>Means and aids for conveying persons and/or goods Examples: roads, airports/airstrips, shipping routes, tunnels, nautical charts, vehicle or vessel location, aeronautical charts, railways."
        },
        position: {
            target: "mouse",
            adjust: {
                x: 30
            }
        }
    });


	$("option[value=utilitiesCommunication]").qtip({
        content: {
            text: "<b>Utilities/Communication:</b><br/>Energy, water and waste systems andcommunications infrastructure and services Examples: hydroelectricity, geothermal, solar and nuclear sources of energy, water purification and distribution, sewage collection and disposal, electricity and gas distribution, data communication, telecommunication, radio, communication networks."
        },
        position: {
            target: "mouse",
            adjust: {
                x: 30
            }
        }
    });

    function sortOptions(options) {
        return options.sort(function(a,b){
            a = $(a).attr("order");
            b = $(b).attr("order")

            return a-b;
        });
    }
});

function checkSpatial(isNonSpatial) {
    if (isNonSpatial) {
        $("#nonspatial").find(":input").attr("required", "required");
        $("#spatial").find(":input").removeAttr("required");
    } else {
        $("#spatial").find(":input").attr("required", "required");
        $("#nonspatial").find(":input").removeAttr("required");
    }
}
