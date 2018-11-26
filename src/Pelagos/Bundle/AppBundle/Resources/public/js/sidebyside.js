var $ = jQuery.noConflict();

$(document).ready(function()
{
    "use strict";

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
        $("#left").load(getFormUrl + "/" + udi + "/" + version, function() {
            $(".smallmap", this).gMap();
            $(".filetabs", this).tabs();
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
        $("#right").load(getFormUrl + "/" + udi + "/" + version, function() {
            $(".smallmap", this).gMap();
            $(".filetabs", this).tabs();
        });
    });
});