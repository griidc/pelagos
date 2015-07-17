var $ = jQuery.noConflict();

var hashchanged = function()
{
    "use strict";
    var hash = window.location.hash.replace(/^#/, "");
    populateFundingOrganization(hash);
};

$(document).ready(function()
{
    "use strict";
    // Bind the event.
    $(window).hashchange(hashchanged);
    // Trigger the event (useful on page load).
    hashchanged();
});

function populateFundingOrganization(FundingOrganizationID)
{
    "use strict";
    $("#fundingOrganizationForm").trigger("reset");
    $("#fundingOrganizationLogo").html("");
    $.get(pelagosBasePath + "/services/fundingOrganization/" + FundingOrganizationID)
    .done(function(data) {
        $("#fundingOrganizationForm").fillForm(data.data);
        $("#fundingOrganizationLogo").html("<img src=\"" + pelagosBasePath + "/services/fundingOrganization/logo/" + FundingOrganizationID + "\">");
    });
}
