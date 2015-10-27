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
    
    $("#position").autocomplete({
        source: [
            "Administrator",
            "Researcher"
        ]
    });
});