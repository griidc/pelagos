var $ = jQuery.noConflict();

$(document).ready(function()
{
    "use strict";

    var loading = $.Deferred();
    var leftLoaded = false;
    var rightLoaded = false;

    $("#get-versions-button").click(function (){
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

            $(".right-version").find("select option:selected")
                .prop("selected", false)
                .next()
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
        leftLoaded = false;
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
        rightLoaded = false;
        $("#right").load(getFormUrl + "/" + udi + "/" + version, function() {
            $(".smallmap", this).gMap();
            $(".filetabs", this).tabs();
            rightLoaded = true;
            loading.notify();
        });
    });

    loading.progress(function() {
        if (leftLoaded && rightLoaded) {
            showDifferences();
        }
    });

    function showDifferences()
    {
        // Change textarea and input to divs that look like them.
        $("#right")
        .find("textarea,input[type=text]")
        .not(".keywordinput")
        .each(function() {
            makecontentEditable(this);
        });

        $("#right").find(".contentbox")
        .each(function(){
            var thisId = $(this).attr("id");
            var rightText = $(this).text();
            var leftInput = $("#left").find("#"+thisId);
            var leftText = leftInput.val();
            var diff = compareInputs(leftText, rightText);
            $(this).html(diff);
        });

        // Compare keyword selects.
        $("#right").find("select.keywordinput").each(function() {
            var thisId = $(this).attr("id");
            var leftSelect = $("#left").find("#"+thisId);
            var rightSelect = $(this);
            multiSelectCompare(leftSelect, rightSelect);
        });

        // Show differences in other selects.
        $("#right").find("select").not(".keywordinput").each(function() {
            var thisId = $(this).attr("name");
            var leftSelect = $("#left").find('[name="'+thisId+'"]');
            var rightSelect = $(this);
            var leftValue = leftSelect.find("option:selected").text();
            var rightValue = rightSelect.find("option:selected").text();

            var leftValue = leftSelect.val();
            var rightValue = rightSelect.val();

            if (leftValue != rightValue) {
                rightSelect.addClass("deloption");
            } else {
                rightSelect.addClass("insoption");
            }
        });
    }

    function multiSelectCompare(leftSelect, rightSelect)
    {
        var leftSelectText = optionsToLines(leftSelect);
        var rightSelectText = optionsToLines(rightSelect);

        // Compare in Linemode, put each option on a new line.
        var dmp = new diff_match_patch();
        var a = dmp.diff_linesToChars_(leftSelectText, rightSelectText);
        var lineText1 = a.chars1;
        var lineText2 = a.chars2;
        var lineArray = a.lineArray;
        var diffs = dmp.diff_main(lineText1, lineText2, false);
        dmp.diff_charsToLines_(diffs, lineArray);

        rightSelect.find("option").remove();
        $.each(diffs, function() {
            switch (this[0]) {
                case -1:
                    var optionClass = "deloption";
                    break;
                case 1:
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
    }
});

function makecontentEditable(input)
{
    var originalInput = $(input);
    var inputType = originalInput.prop("tagName");
    var inputHeight = originalInput.height();
    var inputWidth = originalInput.width();
    var inputText = originalInput.val();
    var attrs = input.attributes;
    var newElement = $("<div>")
    .attr("tagname", inputType)
    .height(inputHeight)
    .width(inputWidth)
    .text(inputText);

    // Set all the attributes (name, id, etc).
    $.each(attrs, function(index, atribute) {
        newElement.attr(atribute.name, atribute.value);
    });

    newElement.addClass("contentbox");

    originalInput.replaceWith(newElement);
}

function compareInputs(leftText, rightText)
{
    var dmp = new diff_match_patch();
    var d = dmp.diff_main(leftText, rightText);
    dmp.diff_cleanupSemantic(d);

    return dmp.diff_prettyHtml(d);
}

function optionsToLines(input)
{
    return input
    .find("option")
    .map(function(){return this.text;})
    .get()
    .join("\n");
}
