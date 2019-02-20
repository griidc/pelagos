var $ = jQuery.noConflict();

$(document).ready(function()
{
    "use strict";

    var loading = $.Deferred();
    var leftLoaded = false;
    var rightLoaded = false;

    $("#get-versions-button").button().click(function (){
        var udi = $("input[name=udi]").val().trim();
        jQuery.ajax({
            url: Routing.generate("pelagos_app_ui_sidebyside_getversions", {udi: udi}),
            type: "POST",
            data: {udi: udi},
            context: document.body
        })
        .success(function(data) {
            var select = $("select.version-select");
            select.find("option").remove();

            $.each(data, function(index, item) {
                if (typeof item === "object") {
                    var option = new Option(item.sequence, item.sequence);
                    $(option).data("udi", item.udi);
                    $(option).data("modificationtimestamp", item.modificationtimestamp);
                    $(option).data("status", item.status);
                    $(option).data("version", item.version);
                    $(option).data("modifier", item.modifier);
                    select.append(option);
                }
            });

            // Select the very first version in left-hand view. By default
            // latest version is otherwise selected.
            $(".left-version").find("select option:last")
                .prop("selected", "selected");
            select.change();

            // Count the number of options, but divide by 2,
            // because there are two select boxes (with options).
            $("#numversions").text(select.find("option").size() / 2);
            $("#datasetstatus").text(data.datasetstatus);
            $(".udi-title").text(data.udi);
        })
        .error(function() {
            var n = new noty({
                text: "UDI:" + udi + " not found!",
                type: "error",
                theme: "relax",
                timeout: 3000,
                animation: {
                    open: {opacity: "toggle"},  // fadeIn
                    close: {opacity: "toggle"}, // fadeOut
                }
            });
            $("input[name=udi]").val("");
        });
    });

    $(".left-version").find("select").change(function() {
        var version = $(this).find("option:selected").data("version");
        var udi = $(this).find("option:selected").data("udi");

        $("#left").html($(".spinner div").html());
        leftLoaded = false;
        loading.notify();

        $(this).parents("div.left-version")
            .find(".submission-status")
            .text($(this).find("option:selected").data("status"));
        $(this).parents("div.left-version")
            .find(".submission-modificationtimestamp")
            .text($(this).find("option:selected").data("modificationtimestamp"));
        $(this).parents("div.left-version")
            .find(".submission-modifier")
            .text($(this).find("option:selected").data("modifier"));
        var getFormUrl = Routing.generate("pelagos_app_ui_sidebyside_getsubmissionform");
        $("#left").load(getFormUrl + "/" + udi + "/" + version, function() {
            $(".smallmap", this).gMap();
            $(".filetabs", this).tabs();
            leftLoaded = true;
            loading.notify();
        });
    });

    $(".right-version").find("select").change(function() {
        var version = $(this).find("option:selected").data("version");
        var udi = $(this).find("option:selected").data("udi");

        $("#right").html($(".spinner div").html());
        rightLoaded = false;
        loading.notify();

        $(this).parents("div.right-version")
            .find(".submission-status")
            .text($(this).find("option:selected").data("status"));
        $(this).parents("div.right-version")
            .find(".submission-modificationtimestamp")
            .text($(this).find("option:selected").data("modificationtimestamp"));
        $(this).parents("div.right-version")
            .find(".submission-modifier")
            .text($(this).find("option:selected").data("modifier"));
        var getFormUrl = Routing.generate("pelagos_app_ui_sidebyside_getsubmissionform");
        $("#right").load(getFormUrl + "/" + udi + "/" + version, function() {
            $(".smallmap", this).gMap();
            $(".filetabs", this).tabs();
            rightLoaded = true;
            loading.notify();
        });
    });

    $("#show-diff-button").button("option", "disabled", true).click(function (){
        if ($(this).hasClass(".show-diffs")) { //hide
            $(this).removeClass(".show-diffs");
            //trigger change event to reload right panel
            $(".right-version").find("select").change();
        } else { //show
            $(this).addClass(".show-diffs");
            showDifferences();
        }
    });

    loading.progress(function() {
        var showDiffButton = $("#show-diff-button");
        if (leftLoaded && rightLoaded) {
            showDiffButton.button("enable");
        } else {
            showDiffButton.button("disable");
        }
    });

    function showDifferences()
    {
        // Change textarea and input to divs that look like them.
        $("#right")
        .find("textarea,input[type=text]")
        .not(".keywordinput")
        .each(function() {
            var originalInput = $(this);
            var newElement = $("<div>")
            .attr("tagname", originalInput.prop("tagName"))
            .height(originalInput.height())
            .width(originalInput.width())
            .text(originalInput.val());

            // Set all the attributes (name, id, etc).
            $.each(this.attributes, function(index, atribute) {
                newElement.attr(atribute.name, atribute.value);
            });

            newElement.addClass("contentbox");
            originalInput.replaceWith(newElement);
        });

        $("#right").find(".contentbox")
        .each(function(){
            var rightInput = $(this);
            var leftInput = $("#left").find("#" + rightInput.attr("id"));

            if (leftInput.length === 0) {
                return;
            }

            // Comparing Text
            var dmp = new diff_match_patch();
            var diffs = dmp.diff_main(leftInput.val(), rightInput.text());
            dmp.diff_cleanupSemantic(diffs);

            // Setting the HTML with diff.
            $(this).html(dmp.diff_prettyHtml(diffs));
        });

        // Compare keyword selects.
        $("#right").find("select.keywordinput").each(function() {
            var rightSelect = $(this);
            var leftSelect = $("#left").find("#" + rightSelect.attr("id"));

            if (leftSelect.length === 0) {
                return;
            }

            // Convert option list to lines.
            var leftSelectText = leftSelect
            .find("option")
            .map(function(){return this.text;})
            .get().join("\n");
            var rightSelectText = rightSelect
            .find("option")
            .map(function(){return this.text;})
            .get().join("\n");

            // Compare in Linemode, put each option on a new line.
            var dmp = new diff_match_patch();
            var lineToChars = dmp.diff_linesToChars_(leftSelectText, rightSelectText);
            var diffs = dmp.diff_main(lineToChars.chars1, lineToChars.chars2, false);
            dmp.diff_charsToLines_(diffs, lineToChars.lineArray);

            rightSelect.find("option").remove();
            $.each(diffs, function() {
                switch (this[0]) {
                    case -1: // diff_match_patch.DIFF_DELETE
                        var optionClass = "deloption";
                        break;
                    case 1: // diff_match_patch.DIFF_INSERT
                        var optionClass = "insoption";
                        break;
                    default:
                        var optionClass;
                }
                var options = this[1].trim().split("\n");
                $.each(options, function() {
                    var optionText = this;
                    var option = new Option(optionText, optionText);
                    $(option).addClass(optionClass);
                    rightSelect.append(option);
                });
            });
        });

        // Show differences in other selects.
        $("#right").find("select").not(".keywordinput").each(function() {
            var rightSelect = $(this);
            var leftSelect = $("#left").find('[name="' + rightSelect.attr("name") + '"]');

            if (leftSelect.length === 0) {
                rightSelect.addClass("insoption");
                return;
            }

            if (leftSelect.val() !== rightSelect.val()) {
                rightSelect.addClass("deloption");
            } else {
                rightSelect.addClass("insoption");
            }
        });
    }
});
