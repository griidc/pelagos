$(document).ready(function()
{
});

function startDownload(id)
{
    if ($("#download_splash").length) {
        return;
    }

    $('<div id="download_splash" />').appendTo("#pelagos-content").load(
        Routing.generate("pelagos_app_download_default", {"id": id}),
        function () {
            initializeDownload(id);
        }
    );
}

function initializeDownload(id)
{
    $("#download_splash .dl_button[title]").qtip({
        position: {
            viewport: $(window),
            my: "bottom left",  // position of arrow
            at: "top right"     // position relative to selector
        },
        style: {
            classes: "qtip-shadow qtip-tipped customqtip"
        }
    });
    $("#download_splash .close_button").click(function () {
        closeSplashScreen();
    });

    $.getJSON(Routing.generate("pelagos_app_download_http", {"id": id}), function (data) {
        $(".qtip").hide();
        $("#download_splash #download-link").attr("href", (data['downloadUrl']));
    });
}

function closeSplashScreen()
{
    $("#download_splash").remove();
    $.cookie("dl_attempt_cookie", null, { path: "/", domain: location.hostname });
}
