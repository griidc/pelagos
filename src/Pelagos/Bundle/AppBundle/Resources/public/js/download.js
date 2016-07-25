$(document).ready(function()
{
    // If cookie is set remove it and initiate download
    if (typeof $.cookie("dl_attempt_cookie") != "undefined") {
        var dl_cookie = $.cookie("dl_attempt_cookie");
        $.cookie("dl_attempt_cookie", null, { path: "/", domain: location.hostname });
        if (dl_cookie != null) {
            console.log(dl_cookie);
            startDownload(dl_cookie);
        }
    }
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
    if ($("#download_splash #pre_login").length) {
        $.cookie("dl_attempt_cookie", id, { expires: 1, path: "/", domain: location.hostname });
    } else {
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
    }
    $("#download_splash .close_button").click(function () {
        closeSplashScreen();
    });
    $("#download_splash #http_download_button").click(function () {
        $(".qtip").hide();
        $('#dataset_download_content').load(
            Routing.generate("pelagos_app_download_http", {"id": id})
        );
    });
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
