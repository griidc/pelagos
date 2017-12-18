var $ = jQuery.noConflict();
var geowizard;

$(document).ready(function(){
    "use strict";

    $("#udiLoadReviewform").bind("change keyup mouseout", function() {
        var udiTextBox = $("#udiReview");
        if($(this).valid() && udiTextBox.val() !== "" && udiTextBox.is(":disabled") === false) {
            $("#loadReviewButton").button({
                disabled: false
            });
        } else {
            $("#loadReviewButton").button({
                disabled: true
            });
        }
    });

    $(".pelagosNoty").pelagosNoty({timeout: 0, showOnTop:false});
    $("html").show();

    $("label").next("input[required],textarea[required],select[required]").prev().addClass("emRequired");

    $("button").button();

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
        if (activeTab == 0) {
            btnPrevious.button("disable");
            btnPrevious.hide();
        } else {
            btnPrevious.show();
            btnPrevious.button("enable");
        }
        if (activeTab == 5) {
            btnNext.button("disable");
            btnNext.hide();
        } else {
            btnNext.show();
            btnNext.button("enable");
        }
    });

    $(".pelagosNoty").pelagosNoty({timeout: 0, showOnTop:false});

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
});

function checkSpatial(isNonSpatial) {
    if (isNonSpatial) {
        $("#nonspatial").find(":input").attr("required", "required");
        $("#spatial").find(":input").removeAttr("required");
        $("#spatialExtras").hide().find(":input").removeAttr("required").val("");
    } else {
        $("#spatial").find(":input").attr("required", "required");
        $("#nonspatial").find(":input").removeAttr("required");
        $("#spatialExtras").show().find(":input").attr("required", "required");
    }
}
