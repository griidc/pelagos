var $ = jQuery.noConflict();

$(document).ready(function()
{
    "use strict";
    
    
    // $("#left").load("/pelagos-symfony/dev/mvde/sidebyside/getForm/R1.x135.120:0003/0");
    // $("#right").load("/pelagos-symfony/dev/mvde/sidebyside/getForm/R1.x135.120:0003/13");
    
    
    $("#left-version").change(function() {
        var version = $(this).val();
        var version = $(this).find("option:selected").data("version");
        var udi = $(this).find("option:selected").data("udi");
        $("#left").load("/pelagos-symfony/dev/mvde/sidebyside/getForm/" + udi + "/" + version);
        //
    });
    
    $("#right-version").change(function() {
        var version = $(this).val();
        var version = $(this).find("option:selected").data("version");
        var udi = $(this).find("option:selected").data("udi");
        $("#right").load("/pelagos-symfony/dev/mvde/sidebyside/getForm/" + udi + "/" + version);
        //
    });
    
    $("#left-version").change();
    $("#right-version").change();
    
});