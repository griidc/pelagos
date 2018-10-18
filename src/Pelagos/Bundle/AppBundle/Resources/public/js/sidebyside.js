var $ = jQuery.noConflict();

$(document).ready(function()
{
    "use strict";
    
    $(".left-version").find("select").change(function() {
        var version = $(this).find("option:selected").data("version");
        var udi = $(this).find("option:selected").data("udi");
        
        $("#left").html("<h1>LOADING</h1>");
        
        $(this).parents("div.left-version")
            .find(".submission-status")
            .text($(this).find("option:selected").data("status"));
        $(this).parents("div.left-version")
            .find(".submission-modificationtimestamp")
            .text($(this).find("option:selected").data("modificationtimestamp"));
        $(this).parents("div.left-version")
            .find(".submission-modifier")
            .text($(this).find("option:selected").data("modifier"));
        
        $("#left").load("/pelagos-symfony/dev/mvde/sidebyside/getForm/" + udi + "/" + version, function() {
            $(".smallmap", this).gMap();
        });
    });
    
    $(".right-version").find("select").change(function() {
        var version = $(this).find("option:selected").data("version");
        var udi = $(this).find("option:selected").data("udi");
        
        $("#right").html("<h1>LOADING</h1>");
        
        $(this).parents("div.right-version")
            .find(".submission-status")
            .text($(this).find("option:selected").data("status"));
        $(this).parents("div.right-version")
            .find(".submission-modificationtimestamp")
            .text($(this).find("option:selected").data("modificationtimestamp"));
        $(this).parents("div.right-version")
            .find(".submission-modifier")
            .text($(this).find("option:selected").data("modifier"));
        
        $("#right").load("/pelagos-symfony/dev/mvde/sidebyside/getForm/" + udi + "/" + version, function() {
            $(".smallmap", this).gMap();
        });
    });
    
    $(".left-version select").change();
    $(".right-version select").change();
    
    
    
});