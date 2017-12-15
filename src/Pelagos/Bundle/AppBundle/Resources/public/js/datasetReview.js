var $ = jQuery.noConflict();
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

    $("#dtabs").tabs({
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


    $("#btn-previous").click(function() {
        var activeTab = $("#dtabs").tabs("option","active");
        activeTab--;
        if (activeTab < 0) {activeTab = 0};
        $("#dtabs").tabs({active:activeTab});
    }).button("disable");

    $("#btn-next").click(function() {
        var activeTab = $("#dtabs").tabs("option","active");
        activeTab++;
        $("#dtabs").tabs({active:activeTab});
    });

    $("#dtabs").on("active", function() {
        var activeTab = $("#dtabs").tabs("option","active");
        if (activeTab == 0) {
            $("#btn-previous").button("disable");
            $("#btn-previous").hide();
        } else {
            $("#btn-previous").show();
            $("#btn-previous").button("enable");
        }
        if (activeTab == 5) {
            $("#btn-next").button("disable");
            $("#btn-next").hide();
        } else {
            $("#btn-next").show();
            $("#btn-next").button("enable");
        }
    });
});