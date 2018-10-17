var $ = jQuery.noConflict();

$(document).ready(function()
{
    "use strict";
    
    $(".left-version").find("select").change(function() {
        var version = $(this).find("option:selected").data("version");
        var udi = $(this).find("option:selected").data("udi");
        
        $(this).parents("div.left-version")
            .find(".submission-status")
            .text($(this).find("option:selected").data("status"));
        $(this).parents("div.left-version")
            .find(".submission-modificationtimestamp")
            .text($(this).find("option:selected").data("modificationtimestamp"));
        $(this).parents("div.left-version")
            .find(".submission-modifier")
            .text($(this).find("option:selected").data("modifier"));
        
        $("#left").load("/pelagos-symfony/dev/mvde/sidebyside/getForm/" + udi + "/" + version);
    });
    
    $(".right-version").find("select").change(function() {
        var version = $(this).find("option:selected").data("version");
        var udi = $(this).find("option:selected").data("udi");
        
        $(this).parents("div.right-version")
            .find(".submission-status")
            .text($(this).find("option:selected").data("status"));
        $(this).parents("div.right-version")
            .find(".submission-modificationtimestamp")
            .text($(this).find("option:selected").data("modificationtimestamp"));
        $(this).parents("div.right-version")
            .find(".submission-modifier")
            .text($(this).find("option:selected").data("modifier"));
        
        $("#right").load("/pelagos-symfony/dev/mvde/sidebyside/getForm/" + udi + "/" + version);
    });
    
    $(".left-version select").change();
    $(".right-version select").change();
    
});