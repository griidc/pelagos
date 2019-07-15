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

    $("#download_splash #download-link").attr("href", Routing.generate("pelagos_app_download_http", {"id": id}));
    $("#download_splash #gridftp_download_button").click(function () {
        $(".qtip").hide();
        $('#dataset_download_content').load(
            Routing.generate("pelagos_app_download_gridftp", {"id": id})
        );
    });
}

function closeSplashScreen()
{
    $("#download_splash").remove();
    $.cookie("dl_attempt_cookie", null, { path: "/", domain: location.hostname });
}
