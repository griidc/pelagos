$(document).ready(function()
{
    "use strict";
    
    /* LISTS ARE PLACEHOLDERS! */
    
    $("#organization").autocomplete({
        source: [
            "GRIIDC",
            "GOMRI",
            "Harte",
            "Texas A&M"
        ]
    });
    
    $("#role").autocomplete({
        source: [
            "Administrator",
            "Researcher"
        ]
    });
});