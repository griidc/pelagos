var $ = jQuery.noConflict();

var spinner;
var target;
var formHash;
var difValidator;
var imgWarning;
var imgInfo;
var imgCross;
var imgTick;
var imgError;
var imgFolder;
var imgFolderGray;
var imgThrobber;
var imgCancel;

$(document).ready(function()
{
    $("#pelagos-content > table > tbody > tr > td:last-child").height($("#pelagos-content > table > tbody > tr > td:first-child").height());

    // Add emRequired class to each field that is required.
    $("label").next("input[required],textarea[required],select[required]").prev().addClass("emRequired");

    $('[name="primaryPointOfContact"],[name="secondaryPointOfContact"]').prop("disabled",true);

    // Getting Assetic Image paths
    imgWarning = $("#imgwarning").attr("src");
    imgInfo = $(".info").attr("src");
    imgCross = $("#imgcross").attr("src");
    imgTick = $("#imgtick").attr("src");
    imgError = $("#imgerror").attr("src");
    imgFolder = $("#imgfolder").attr("src");
    imgFolderGray = $("#imgfoldergray").attr("src");
    imgThrobber = $("#imgthrobber").attr("src");
    imgCancel = $("#imgCancel").attr("src");

    initSpinner();

    //Setup qTip
    $.fn.qtip.defaults = $.extend(true, {}, $.fn.qtip.defaults, {
        position: {
            viewport: $(window),
            my: "bottom left",
            at: "top right",
        },
        style: {
            classes: "qtip-shadow qtip-tipped customqtip"
        }
    });

    // load qTip descriptions
    $("img.info").each(function() {
        $(this).qtip({
            content: {
                text: $(this).next(".tooltiptext")
            }
        });
    });
    $(".statusicon[title]").qtip();

    // set up DatePickers
    $("#estimatedStartDate").datepicker({
        //defaultDate: "",
        //showOn: "button",
        dateFormat: "yy-mm-dd",
        changeMonth: true,
        changeYear: true,
        numberOfMonths: 3,
        stepMonths: 3,
        showButtonPanel: false,
        onClose: function(selectedDate) {
            $("#estimatedEndDate").datepicker("option", "minDate", selectedDate);
        }
    });
    $("#estimatedEndDate").datepicker({
        //defaultDate: "+1w",
        //showOn: "button",
        dateFormat: "yy-mm-dd",
        changeMonth: true,
        changeYear: true,
        numberOfMonths: 3,
        stepMonths: 3,
        showButtonPanel: false,
        onClose: function(selectedDate) {
            $("#estimatedStartDate").datepicker("option", "maxDate", selectedDate);
        }
    });

    $("#btnDS").button({
        disabled : true
    }).click(function() {
        var submissionUrl = Routing.generate("pelagos_app_ui_datasetsubmission_default") + "?regid=" + $("[name=udi]").val();
        window.location.href = submissionUrl;
    });

    $("#btnSubmit").button().click(function() {
        $("#btn").val($(this).val());
        //$("#status").val("Open");
        $("#difForm").submit();
    });

    $("#btnSave").button().click(function() {
        $("#btn").val($(this).val());
        //$("#status").val("Open");
        $("#difForm").submit();
    });

    $("#btnReset").button().click(function() {
        formReset();
    });

    $("#btnTop").button().click(function() {
        scrollToTop();
    });

    $("#btnApprove").button().click(function() {
        $("#btn").val($(this).val());
        $("#difForm").submit();
    });

    $("#btnReject").button().click(function() {
        difStatus($('#difForm [name="id"]').val(), "reject");
    });

    $("#btnUpdate").button().click(function() {
        $("#btn").val($(this).val());
        $("#difForm").submit();
    });

    $("#btnUnlock").button().click(function() {
        difStatus($('#difForm [name="id"]').val(), "unlock");
    });

    $("#btnReqUnlock").button().click(function() {
        difStatus($('#difForm [name="id"]').val(), "request-unlock");
    });

    $("#btnSearch").button().click(function () {
        treeSearch();
    });

    $("#researchGroup").change(function(){
        loadPOCs($(this).val());
    });

    loadDIFS("", null, true);

    jQuery.validator.addMethod("trueISODate", function(value, element) {
        var regPattern = /^\d{4}-\d{1,2}-\d{1,2}$/
        return this.optional(element) || ((Date.parse(value)) && regPattern.test(value));
    },function (params, element) {
        return "Please enter a valid ISO Date"
    });

    difValidator = $("#difForm").validate({
        ignore: ".ignore",
        messages: {
            geoloc: "Click on Spatial Wizard Button!",
            estimatedStartDate: {
                required: "Start Date is a required field."
            },
            estimatedEndDate: {
                required: "End Date is a required field."
            }
        },
        submitHandler: function(form) {
            saveDIF(form);
        },
        rules: {
            estimatedStartDate: "trueISODate",
            estimatedEndDate: "trueISODate",
            privacyother: {
                required: {
                    depends: function(element)
                    {
                        return ($("#difPrivacy:checked").val() == "Yes" || $("#difPrivacy:checked").val() == "Uncertain");
                    }
                }
            }
        }

    });

    $("#spatialExtentGeometry").change(function() {
        geowizard.haveGML($(this).val());
    });

    $("#difForm").change(function() {
        if (typeof formHash == "undefined"){formHash = "";}
    });

    $("#fltReset").button().click(function (){
        $("#fltStatus").val("");
        $("#fltResearcher").val("");
        $("#fltResults").val("");

        $('[name="showempty"][value="1"]').prop("checked",true);
       treeFilter();
    });

    $("#fltStatus").change(function () {
        treeFilter();
    });

    $('[name="showempty"]').change(function()
    {
       treeFilter();
    });

    $("#status").change(function(){
        $("#btnDS").button("option", "disabled", true);
        if ($(this).val() == "closedout") {
            var html = '<fieldset><img src="' + imgCross +'">&nbsp;Research Group (locked)';
            html += '<div class="substatus"><i>A DIF cannot be submitted for this research group because the grant has been closed out.<br>Please contact GRIIDC at <a href="mailto:griidc@gomri.org">griidc@gomri.org</a> if you would like to submit a DIF or have any questions.</i></div>';
            html += '</fieldset>';
            $("#statustext").html(html);
            formHash = $("#difForm").serialize();
        }
        else if ($('[name="udi"]').val() != "")
        {
            if ($(this).val() == "0")
            {
                $("#statustext").html('<fieldset><img src="' + imgCross +'">&nbsp;DIF saved but not yet submitted</fieldset>');
            }
            else if ($(this).val() == "1")
            {
                $("#statustext").html('<fieldset><img src="' + imgError +'">&nbsp;DIF submitted for review (locked)</fieldset>');
            }
            else if ($(this).val() == "2")
            {
                $("#statustext").html('<fieldset><img src="' + imgTick +'">&nbsp;DIF approved (locked)</fieldset>');
                $("#btnDS").button("option", "disabled", false);
            }
            $("#researchGroup").prop("disabled", true);
            formHash = $("#difForm").serialize();
        }
        else
        {
            $("#statustext").html("");
            $("#researchGroup").prop("disabled", false);
        }
    });

    $("#udi").change(function(){
        if ($('[name="udi"]').val() != "")
        {
            $("#udilabel").text($('[name="udi"]').val()); $("#udidiv").show();
        }
        else
        {
            $("#udidiv").hide();
        }
    });

    geowizard = new MapWizard({"divSmallMap":"difMap","divSpatial":"spatial","divNonSpatial":"nonspatial","divSpatialWizard":"spatwizbtn","gmlField":"spatialExtentGeometry","descField":"spatialExtentDescription","spatialFunction":""});

    $("#spatialExtentGeometry").change(function(){
        if ($("#spatialExtentDescription").val()!="" && $("#spatialExtentGeometry").val()=="")
        { geowizard.haveSpatial(true);}
        else
        { geowizard.haveSpatial(false); }

        if ($("#spatialExtentGeometry").val()!="")
        { geowizard.haveSpatial(false); }
    });

    $.ajaxSetup({
        error: function(x, t, m) {
            var message;
            if (typeof m.message != "undefined") {
                message = m.message;}else{message = m;
            }
            if ((x.status == 400 || x.status == 403) && x.responseJSON) {
                message = x.responseJSON.message;
            }
            console.log("Error in Ajax:"+t+", Message:"+message)
        }
    });

    var $_GET = getQueryParams(document.location.search);
    if (typeof $_GET["id"] != "undefined") {
        var udi = $_GET["id"];
        var url =  $("#difForm").attr("dataset") + "?udi=" + udi + "&_properties=dif.id";
        $.get(url, function(data) {
            if (data.length == 1) {
                getNode(udi, data[0].dif.id);
            }
        });
    }
});

function difStatus(id, status)
{
    var url = $("#difForm").attr("action") + "/" + id + "/" + status;

    udi = $('#difForm [name="udi"]').val();

    var message = '<div><img src="' + imgInfo + '"><p>';

    switch (status) {
        case "approve":
            var msgtext  = "The application with DIF ID: " + udi + " was successfully approved!";
            var msgtitle = "DIF Approved";
            break;
        case "reject":
            var msgtext  = "The application with DIF ID: " + udi + " was successfully rejected!";
            var msgtitle = "DIF Rejected";
            break;
        case "unlock":
            var msgtext  = "Successfully unlocked DIF with ID: " + udi + ".";
            var msgtitle = "DIF Unlocked";
            break;
        case "request-unlock":
            var msgtext  = "Your unlock request has been submitted for ID: " + udi + ".<br>Your unlock request will be reviewed by GRIIDC staff.<br>You will receive an e-mail when the DIF is unlocked.";
            var msgtitle = "DIF Unlock Request Submitted";
            break;
    }

    message += msgtext + "</p></div>";

    $.when(formChanged()).done(function() {
        showSpinner();
        $.ajax({
            url: url,
            type: "PATCH",
            success: function(json, textStatus, jqXHR) {
                hideSpinner();
                formReset(true);

                $("<div>"+message+"</div>").dialog({
                    autoOpen: true,
                    resizable: false,
                    minWidth: 300,
                    height: "auto",
                    width: "auto",
                    modal: true,
                    title: msgtitle,
                    buttons: {
                        OK: function() {
                            $(this).dialog("close");
                            scrollToTop();
                            treeFilter();
                            return $.Deferred().resolve();
                        }
                    }
                });
            },
            error: function(x, t, m) {
                var errorMessage;
                if (typeof m.message != "undefined") {
                    errorMessage = m.message;}else{message = m;
                }
                if (x.status == 400 || x.status == 403) {
                    errorMessage = x.responseJSON.message;
                }
                $("#spinner").hide();
                $("<div>"+errorMessage+"</div>").dialog({
                    autoOpen: true,
                    height: "auto",
                    resizable: false,
                    minWidth: 300,
                    title: "Error",
                    modal: true,
                    buttons: {
                        OK: function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
    });
}

function getQueryParams(qs) {
    qs = qs.split("+").join(" ");
    var params = {},
        tokens,
        re = /[?&]?([^=]+)=([^&]*)/g;

    while (tokens = re.exec(qs)) {
        params[decodeURIComponent(tokens[1])]
            = decodeURIComponent(tokens[2]);
    }

    return params;
}

function treeSearch()
{
    var searchValue = $("#fltResults").val().trim();
    showSpinner();
    $("#diftree").on("search.jstree", function (e, data) {
        if (data.res.length <= 0)
        {
            $("#noresults").dialog({
                resizable: false,
                modal: true,
                buttons: {
                    "OK": function() {
                        $(this).dialog("close");
                    }
                }
            });
        }
    });

    $("#diftree").jstree(true).search(searchValue);

    hideSpinner();
}

function setFormStatus()
{
    var Status = $("#status").val();
    var isAdmin =  $("#isadmin").val();
    if (isAdmin != "1")
    { $("#btnReqUnlock").hide(); }

    if (Status == "0")
    {
        $("form :input").not(":hidden").prop("disabled",false);
        $("#btnSubmit").prop("disabled",false);
        $("#btnSave").prop("disabled",false);

    }
    else if (isAdmin != "1")
    {
        $("form :input").not(":hidden,#btnReset").prop("disabled",true);
        $("#btnSubmit").prop("disabled",true);
        $("#btnSave").prop("disabled",true);
        if (Status == "2")
        {
          $("#btnReqUnlock").show();
        }
    }

}

function scrollToTop()
{
    $("#page-wrapper").animate({ scrollTop: 0 }, "fast");
}

function saveDIF(form)
{
    if ($('[name="udi"]', form).val() != "") {
        $("#researchGroup", form).attr("disabled", false);
        updateDIF(form);
    } else {
        createDIF(form);
    }

}

function createDIF(form)
{
    var Form = $(form);
    var formData = $(form).serialize(); //new FormData(form);
    var url = $(form).attr("action");
    var method = $(form).attr("method");

    var resourceLocation= "";
    var udi = "";
    var resourceId = "";
    var response   = { status: "", message: ""};
    var buttonValue = $('[name="button"]', form).val();

    showSpinner();
    formHash = Form.serialize();
    $.ajax({
        url: url,
        type: method,
        datatype: "json",
        data: formData
    }).done(function (json, textStatus, jqXHR) {
        // Saving the DIF
        if (jqXHR.status === 201) {
            resourceLocation = jqXHR.getResponseHeader("location");
        } else {
            resourceLocation = url;
        }
        response.status = "success";
    }).fail(function (json, text, jqXHR) {
        var errorMessage = JSON.parse(json.responseText);
        response.status = "error";
        response.message = errorMessage.message;
    })
    .then(function() {
        // Getting the Resource
        return $.ajax({
            url: resourceLocation,
            datatype: "json",
            type: "GET",
            success: function(json, textStatus, jqXHR) {
                // Got the Resource, setting variables
                resourceId = json.id;
                udi = json.dataset.udi;
            }
        })
    })
    .then(function() {
        // Update the status if submit was pressed
        if (buttonValue === "submit") {
            // It was the submit button
            return $.ajax({
                url: resourceLocation +"/submit",
                type: "PATCH",
                datatype: "json",
                data: formData
            }).done(function(json, textStatus, jqXHR) {
                if (jqXHR.status === 204) {
                    response.status = "success";
                }
            }).fail(function (json, text, jqXHR) {
                var errorMessage = JSON.parse(json.responseText);
                response.status = "error";
                response.message = errorMessage.message;
            });
        } else {
            // Not the submit button, still resolve.
            return $.Deferred().resolve();
        }
    })
    .always(function() {
        if (response.status === "success") {
            // Then show the dialog according the how it was saved.
            if (buttonValue === "save") {
                var title = "New DIF Created";
                var message = '<div><img src="' + imgInfo + '"><p>You have saved a DIF. This DIF has been given the ID: ' + udi +"<br>In order to submit your dataset to GRIIDC you must return to this page and submit the DIF for review and approval.</p></div>";
            } else {
                var title = "New DIF Submitted";
                var message = '<div><img src="' + imgInfo + '">' +
                    "<p>Congratulations! You have successfully submitted a DIF to GRIIDC. The UDI for this dataset is " + udi + "." +
                    "<br>The DIF will now be reviewed by GRIIDC staff and is locked to prevent editing. To make changes" +
                    "<br>to your DIF, please email GRIIDC at griidc@gomri.org with the UDI for your dataset." +
                    "<br>Please note that you will receive an email notification when your DIF is approved.</p></div>";
            }
        } else if (response.status === "error") {
            var title = "Unable to perform desired action on DIF";
            var message = '<div><img src="' + imgCancel + '">' +
                    "<p>The application with DIF ID: " + udi + " failed to complete action!" +
                    "<br>Error message: " + response.message + "</p></div>";
        }

        hideSpinner();
        formReset(true);
        //loadDIFS();

        $("<div>"+message+"</div>").dialog({
            autoOpen: true,
            resizable: false,
            minWidth: 300,
            height: "auto",
            width: "auto",
            modal: true,
            title: title,
            buttons: {
                OK: function() {
                    $(this).dialog("close");
                    scrollToTop();
                    treeFilter();
                    return $.Deferred().resolve();
                }
            }
        });
    });
}

function updateDIF(form)
{
    var Form = $(form);
    var formData = $(form).serialize(); //new FormData(form);
    var url = $(form).attr("action");
    var method = $(form).attr("method");

    var resourceLocation= "";
    var udi = $('[name="udi"]', form).val();
    var resourceId = $('[name="id"]', form).val();
    var response   = { status: "", message: ""};
    var buttonValue = $('[name="button"]', form).val();

    if (udi != "") {
        method = "PUT"
        url = url + "/" + resourceId;
    }

    showSpinner();
    formHash = Form.serialize();
    $.ajax({
        url: url,
        type: method,
        datatype: "json",
        data: formData,
        success: function(json, textStatus, jqXHR) {
            // Saving the DIF
            if (jqXHR.status === 201) {
                resourceLocation = jqXHR.getResponseHeader("location");
            } else {
                resourceLocation = url;
            }
            response.status = "success";
        }
    })
    .then(function() {
        // Update the status if submit was pressed
        if (buttonValue === "submit") {
            // It was the submit button
            return $.ajax({
                url: url +"/submit",
                type: "PATCH",
                datatype: "json",
                data: formData,
                success: function(json, textStatus, jqXHR) {
                    if (jqXHR.status === 204) {
                        response.status = "success";
                    }
                }
            });
        } else if (buttonValue === "approve") {
            // It was the approve button
            return $.ajax({
                url: url +"/approve",
                type: "PATCH",
                datatype: "json",
                data: formData
            }).done(function(json, textStatus, jqXHR) {
                if (jqXHR.status === 204) {
                    response.status = "success";
                }
            }).fail(function (json, text, jqXHR) {
                var errorMessage = JSON.parse(json.responseText);
                response.status = "error";
                response.message = errorMessage.message;
            });
        } else {
            // Not the submit button, still resolve.
            return $.Deferred().resolve();
        }
    })
    .always(function() {
        if (response.status === "success") {
            // Then show the dialog according the how it was saved.
            if (buttonValue === "save") {
                var title = "DIF Saved";
                var message = '<div><img src="' + imgInfo + '"><p>Thank you for saving DIF with ID:  ' + udi
                    + ".<br>Before submitting this dataset you must return to this page and submit the dataset information form.</p></div>";
            } else if (buttonValue === "update") {
                var title = "DIF Updated";
                var message = '<div><img src="' + imgInfo + '"><p>Thank you for updating DIF with ID:  ' + udi + ".</p></div>";
            } else if (buttonValue === "submit") {
                var title = "DIF Submitted";
                var message = '<div><img src="' + imgInfo + '">' +
                    "<p>Congratulations! You have successfully submitted a DIF to GRIIDC. The UDI for this dataset is " + udi + "." +
                    "<br>The DIF will now be reviewed by GRIIDC staff and is locked to prevent editing. To make changes" +
                    "<br>to your DIF, please email GRIIDC at griidc@gomri.org with the UDI for your dataset." +
                    "<br>Please note that you will receive an email notification when your DIF is approved.</p></div>";
            } else if (buttonValue === "approve") {
                var title = "DIF Updated and Approved";
                var message = '<div><img src="' + imgInfo + '">' +
                    "<p>The application with DIF ID: " + udi + " was successfully updated and approved!" +
                    "<br></p></div>";
            }
        } else if (response.status === "error") {
            if (response.message === "Can only approve a submitted DIF") {
                var title = "Unable to approve DIF";
                var message = '<div><img src="' + imgCancel + '">' +
                    "<p>The application with DIF ID: " + udi + " cannot be approved as it is already approved!" +
                    "<br></p></div>";
            } else {
                var title = "Unable to perform desired action on DIF";
                var message = '<div><img src="' + imgCancel + '">' +
                    "<p>The application with DIF ID: " + udi + " failed to complete action!" +
                    "<br></p></div>";
            }
        } else {
            var title = "Unable to process DIF form";
            var message = "<div><p>There was an error processing your request. Your session might have expired.<br>" +
                "If the problem still persists after you re-login, please contact the administrator.</p></div>";
        }

        hideSpinner();
        formReset(true);
        //loadDIFS();

        $("<div>" + message + "</div>").dialog({
            autoOpen: true,
            resizable: false,
            minWidth: 300,
            height: "auto",
            width: "auto",
            modal: true,
            title: title,
            buttons: {
                OK: function () {
                    $(this).dialog("close");
                    scrollToTop();
                    treeFilter();
                    return $.Deferred().resolve();
                }
            }
        });
    });
}

function formReset(dontScrollToTop)
{
    $.when(formChanged()).done(function() {
        $("#difForm").trigger("reset");
        $("#udi").val("").change();
        $("#spatialExtentGeometry").val("").change();
        $("#spatialExtentDescription").val("").change();
        $("#status").val("Open").change();
        //formHash = $("#difForm").serialize();
        formHash = undefined;
        geowizard.cleanMap();
        $("form :input").prop("disabled",false);
        $("#btnSubmit").prop("disabled",false);
        $("#btnSave").prop("disabled",false);
        $("#btnDS").button("option", "disabled", true);
        $("#btnReqUnlock").hide();
        geowizard.haveSpatial(false);
        if (!dontScrollToTop){scrollToTop();}
        difValidator.resetForm();
    });
}

function treeFilter()
{
    var difTreeHTML = '<a class="jstree-anchor" href="#"><img src="' + imgThrobber + '"> Loading...</a>';
    $("#diftree").html(difTreeHTML);
    $("#diftree").jstree("destroy");
    loadDIFS($("#fltStatus").val(),$("#fltResearcher").val(),$("[name='showempty']:checked").val())
}

function initSpinner()
{
    var opts = {
        lines: 13, // The number of lines to draw
        length: 40, // The length of each line
        width: 15, // The line thickness
        radius: 50, // The radius of the inner circle
        corners: 1, // Corner roundness (0..1)
        rotate: 0, // The rotation offset
        direction: 1, // 1: clockwise, -1: counterclockwise
        color: "#000", // #rgb or #rrggbb or array of colors
        speed: 1, // Rounds per second
        trail: 60, // Afterglow percentage
        shadow: true, // Whether to render a shadow
        hwaccel: true, // Whether to use hardware acceleration
        className: "spinner", // The CSS class to assign to the spinner
        zIndex: 2000000000, // The z-index (defaults to 2000000000)
        top: "50%", // Top position relative to parent
        left: "50%" // Left position relative to parent
    };

    target = document.getElementById("spinner");
    spinner = new Spinner(opts).spin(target);
}

function showSpinner()
{
    $("#spinner").show();
}

function hideSpinner()
{
    $("#spinner").hide();
}

function getNode(UDI, ID)
{
    fillForm($("#difForm"),UDI,ID);
}

function loadDIFS(Status, Person, ShowEmpty)
{
    var url = $("#difForm").attr("researchgroup");
    $("#btnSearch").button("disable");
    $.ajax({
        url: url + "?id=" + $("#diftree").data("research-groups")
             + "&_properties=name,datasets.udi,datasets.title,datasets.dif.title,datasets.dif.status",
        type: "GET",
        datatype: "json",
    }).done(function(json) {
        makeTree(Status, Person, ShowEmpty, json);
    });
}

function makeTree(Status, Person, ShowEmpty, json)
{
    var treeData = [];

    if (ShowEmpty == "0") {
        ShowEmpty = false;
    } else {
       ShowEmpty = true;
    }

    $.each(json, function(index, researchGroup) {
        var datasets = [];

        // researchGroup.datasets.sort(
            // function(a, b){
                // return a.udi.toLowerCase() > b.udi.toLowerCase() ? 1 : -1;
            // }
        // );

        $.each(researchGroup.datasets, function(idx, dataset) {
            switch (dataset.dif.status)
            {
                case 0:
                    var icon = imgCross;
                    break;
                case 1:
                    var icon = imgError;
                    break;
                case 2:
                    var icon =  imgTick;
                    break;
                default:
                    var icon = imgCross;
                    break;
            }
            var clickAction = "getNode('" + dataset.udi + "'," + dataset.dif.id + ");";
            var datasetNodeText = "[" + dataset.udi + "] " +
                dataset.dif.title
                    .replace(/[^0-9A-Za-z ]/g, function(c) {return "&#" + c.charCodeAt(0) + ";";});

            var datasetNode = {
                id          : dataset.id,
                text        : datasetNodeText,
                icon        : icon,
                li_attr     : {"title": dataset.dif.title},
                a_attr      : {"onclick": clickAction}
            };

            if (Status != "" && Status != undefined) {
                if (Status == dataset.dif.status) {
                    datasets.push(datasetNode)
                }
            } else {
                datasets.push(datasetNode)
            }
        });

        if ($.isEmptyObject(datasets) === true) {
            var folderIcon = imgFolderGray;
        } else {
            var folderIcon = imgFolder;
        }

        var researchGroup = {
            "text"        : researchGroup.name,
            "icon"        : folderIcon,
            "state"       : {
                opened    : true
            },
            "children": datasets,
            li_attr     : {"title": researchGroup.name}
        };



        if ($.isEmptyObject(datasets) === true && ShowEmpty === false) {
            //treeData.push(researchGroup);
        } else {
            treeData.push(researchGroup);
        }
    });

    $("#diftree").jstree({
        "core" : {"data":treeData},
        "plugins" : ["search","sort"],
        "search" : {
            "case_insensitive" : true,
            "show_only_matches": true,
            "search_leaves_only": true,
            "fuzzy" : false
        },
        "sort": function (a, b) {
            return this.get_text(a) > this.get_text(b) ? 1 : -1;
        },
    });

    $("#diftree")
    .on("loaded.jstree", function (e, data) {
        var searchValue = $("#fltResults").val();
        $("#diftree").jstree(true).search(searchValue);
        $("#btnSearch").button("enable");
    });
}

function loadPOCs(researchGroup,ppoc,spoc)
{
    var url = $("#difForm").attr("personresearchgroup");
    $.ajax({
        url: url,
        type: "GET",
        datatype: "JSON",
        data: {"researchGroup":researchGroup, "_properties": "person", "_orderBy":"person.lastName"}
    }).done(function(json) {
            if (json.length>0)
            {
                var selectedID = 0;
                var selectelement;
                selectelement = $('[name="primaryPointOfContact"],[name="secondaryPointOfContact"]');
                selectelement.find("option").remove().end().append('<option value="">[PLEASE SELECT A CONTACT]</option>').val("");
                $.each(json, function(id, personResearchGroup) {
                    selectelement.append(new Option(
                        personResearchGroup.person.lastName
                            + ", "
                            + personResearchGroup.person.firstName
                            + " (" + personResearchGroup.person.emailAddress + ")",
                        personResearchGroup.person.id
                        )
                    );
                    // if (person.isPrimary == true)
                    // {selectedID = person.ID;}
                });
                if ($("#status").val() == 0 || $("#isadmin").val() == "1")
                {selectelement.prop("disabled",false);};

                if (ppoc > 0)
                {
                   $('[name="primaryPointOfContact"]').val(ppoc);
                   formHash = $("#difForm").serialize();
                }
                else if (selectedID !=0){$('[name="primaryPointOfContact"]').val(selectedID);}
                if (spoc > 0)
                {
                    $('[name="secondaryPointOfContact"]').val(spoc);
                    formHash = $("#difForm").serialize();
                }
                $('[name="primaryPointOfContact"]').addClass("required");
            }
            hideSpinner();
            var researchGroupLocked = $("#researchGroup option[value=" + researchGroup + "]").attr("locked");
            if (researchGroupLocked == "true") {
                $("#status").val("closedout");
                setFormStatus();
            }

            $("#status").change();
    });

    if (researchGroup == "")
    {
        var element = $('[name="primaryPointOfContact"],[name="secondaryPointOfContact"]');
        element.find("option").remove().end().append("<option>[PLEASE SELECT TASK FIRST]</option>").prop("disabled",true);
    }
}

function SortByContact(x,y) {
    return ((x.person.lastName.toLowerCase() == y.person.lastName.toLowerCase()) ? 0 : ((x.person.lastName.toLowerCase() > y.person.lastName.toLowerCase()) ? 1 : -1));
}

function formChanged()
{
    return $.Deferred(function() {
        var self = this;
        if (formHash != $("#difForm").serialize() && typeof formHash !="undefined")
        {
            $('<div><img src="' + imgWarning +'"><p>You will lose all changes. Do you wish to continue?</p></div>').dialog({
                title: "Warning!",
                resizable: false,
                modal: true,
                buttons: {
                    "Continue": function() {
                        $(this).dialog("close");
                        formHash = $("#difForm").serialize();
                        difValidator.resetForm();
                        self.resolve();
                        //fillForm(Form,UDI);
                    },
                    Cancel: function() {
                        $(this).dialog("close");
                        self.reject();
                    }
                }
            });
        }
        else
        {
            self.resolve();
        }
    });
}

function fillForm(Form, UDI, ID)
{
    if (Form == null){form = $("form");}

    $.when(formChanged()).done(function() {

        showSpinner();

        var url = $("#difForm").attr("action");

        $.ajax({
            context: document.body,
            url: url,
            type: "GET",
            datatype: "JSON",
            data: {"id":ID, "_properties": "dataset.researchGroup.id,primaryPointOfContact,secondaryPointOfContact"}
        }).done(function(json) {
            difValidator.resetForm();
            if (json.length == 1) {
                json = json[0];
                $.extend(json, {researchGroup: json.dataset.researchGroup.id});
            }

            $("[name='udi']").val(UDI).change();
            var primaryPointOfContact = null;
            var secondaryPointOfContact = null;

            if (json.primaryPointOfContact != null) {
                var primaryPointOfContact = json.primaryPointOfContact.id
            }

            if (json.secondaryPointOfContact != null) {
                var secondaryPointOfContact = json.secondaryPointOfContact.id
            }

            if (json.estimatedStartDate != null) {
                json.estimatedStartDate = json.estimatedStartDate.date.match(/[^\s]*/)[0];
            }

            if (json.estimatedEndDate != null) {
                json.estimatedEndDate = json.estimatedEndDate.date.match(/[^\s]*/)[0];
            }

            loadPOCs(json.dataset.researchGroup.id, primaryPointOfContact, secondaryPointOfContact);
            $.each(json, function(name,value) {
                var element = $("[name="+name+"]");
                var elementType = element.prop("type");
                switch (elementType)
                {
                    case "radio":
                        $("[name='"+name+"'][value='"+value+"']").prop("checked",true);
                        break;
                    case "checkbox":
                        $("[name='"+name+"']").prop("checked",value);
                        break;
                    case "select":
                        $.each(value, function(index,elvalue) {
                            $("[name='"+name+"'][value='"+elvalue+"']").prop("checked",true);
                        });
                        break;
                    default:
                        $("[name="+name+"]").val(value);
                        $("[name="+name+"]:hidden").change();
                        break;
                }
            });
            formHash = $("#difForm").serialize();
            setFormStatus();
            //hideSpinner();
        });
    });
}
