var $ = jQuery.noConflict();
var geowizard;

//FOUC preventor
$("html").hide();

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

    $("html").show();

    $("label").next("input[required],textarea[required],select[required]").prev().addClass("emRequired");

    $("button").button();

    $("#regForm").validate({
        ignore: ".ignore,.prototype",
        submitHandler: function(form) {
            if ($(".ignore").valid()) {
                formHash = $("#regForm").serialize();
                $("#regForm").prop("unsavedChanges", false);
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
        if (activeTab === 5) {
            btnNext.button("disable");
            btnNext.hide();
        } else {
            btnNext.show();
            btnNext.button("enable");
        }
    });


    $("#ds-submit").on("active", function() {
        $(".invaliddsform").show();
        $(".validdsform").hide();
        $("#regForm select[keyword=target] option").prop("selected", true);
        var imgWarning = $("#imgwarning").attr("src");
        var imgCheck = $("#imgcheck").attr("src");
        var valid = $("#regForm").valid();

        if (false === valid) {
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
                        $(this).find(":input").not(".prototype").each(function() {
                            $(this).valid()
                        });
                        if ($(this).find(":input").not(".prototype").valid()) {
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

        $("#submitButton").button({
            disabled: true
        });
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

    function sortOptions(options) {
        return options.sort(function(a,b){
            a = $(a).attr("order");
            b = $(b).attr("order");

            return a-b;
        });
    }
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



