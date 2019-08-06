$(document).ready(function()
{
});

function startDownload(id)
{
    $('<div id="download_splash" />').appendTo("#pelagos-content").load(
        $.getJSON( Routing.generate("pelagos_app_download_default", {"id": id}), function( data ) {

        }).done(function (data) {
            vex.dialog.open({
                className: "vex-theme-os",
                unsafeMessage: getHtmlForDownload(data),
                buttons: [
                    $.extend({}, vex.dialog.buttons.YES, { text: "Download", click: function($vexContent, event) {
                                if (!data.remotelyHosted) {
                                    $.getJSON(Routing.generate("pelagos_app_download_http", {"id": id}), function (data) {
                                    }).done(function (data) {
                                        window.location = `${data.downloadUrl}`;
                                    });
                                }
                            }}),
                    $.extend({}, vex.dialog.buttons.NO, { text: "Cancel" })
                ]
            })
        }).always(function (data) {
            if (data.remotelyHosted) {
                $(":button[type='submit']").remove();
            }
        })
    );
}

function getHtmlForDownload(data)
{
    let dialogBoxHtml;
    if (data.remotelyHosted) {
        let additionalInfo;
            if (data.dataset.availability === 5) {
                additionalInfo = `<p style="color:#A00">This dataset is restricted for download but is hosted by another 
                website so availability status is not guaranteed to be accurate.<br>To obtain access to this dataset, 
                please click the location link above and follow any instructions provided.</p>`
            } else {
                additionalInfo = `<p>To download this dataset, please use the location link above. 
                Note, this dataset is not hosted at GRIIDC; the site is not under GRIIDC control and 
                GRIIDC is not responsible for the information or links you may find there.</p>`
        }

        dialogBoxHtml = `<div id="dataset_download_content">
                            <h3 style="text-align:center;">The dataset you selected is hosted by an external repository.</h3>
                            <div style="border: 1px solid #aaa; padding: 10px; margin-top: 20px; margin-bottom: 10px; border-radius: 4px;">
                                <p style="margin-top:0">
                                All materials on this website are made available to GRIIDC and in turn to you "as-is." There is no
                                warranty (expressed or implied) to these materials, their title, accuracy, non-infringement of third
                                party rights, or fitness for any particular purpose, including the performance or results you may
                                obtain from their use. Use these materials at your own risk. Under no circumstances shall GRIIDC be
                                liable for any direct, incidental, special, consequential, indirect, or punitive damages that result
                                from the use or the inability to use either this website or the materials available via this website.
                                If you are dissatisfied with any website feature, content, or terms of use, your sole and exclusive
                                remedy is to discontinue use.
                                </p>
                                <p style="margin-top:0">
                                This particular dataset is not hosted directly by GRIIDC, so additional terms and conditions may be
                                imposed by the hosting entity.
                                </p>       
                            </div>
                            <div style="border: 1px solid #aaa; padding: 10px; border-radius: 4px;">
                                <strong>UDI:</strong> ${data.dataset.udi}<br />
                                <strong>File name:</strong> ${data.dataset.filename}<br />
                                <strong>Location:</strong>
                                <a href="${data.fileUri}" target=_BLANK>
                                    ${data.fileUri}
                                </a><br />
                                ${additionalInfo}
                            </div>
                    </div>`;
    } else {
        dialogBoxHtml = `<div id="dataset_download_content">
                             <div style="border: 1px solid #aaa; padding: 10px; margin-top: 15px; border-radius: 4px;">
                                 <p style="margin-top:0">
                                 All materials on this website are made available to GRIIDC and in turn to you "as-is." There is no 
                                 warranty (expressed or implied) to these materials, their title, accuracy, non-infringement of third 
                                 party rights, or fitness for any particular purpose, including the performance or results you may
                                 obtain from their use. Use these materials at your own risk. Under no circumstances shall GRIIDC be
                                 liable for any direct, incidental, special, consequential, indirect, or punitive damages that result
                                 from the use or the inability to use either this website or the materials available via this website.
                                 If you are dissatisfied with any website feature, content, or terms of use, your sole and exclusive
                                 remedy is to discontinue use.
                                 </p>
                             </div>

                             <div style="border: 1px solid #aaa; padding: 10px; border-radius: 4px;">
                                 <strong>UDI:</strong> ${data.dataset.udi}<br />
                                 <strong>File name:</strong> ${data.dataset.filename}<br />
                                 <strong>File size:</strong> ${data.dataset.fileSize}<br />
                                 <strong>SHA256 Checksum:</strong> ${data.dataset.checksum}<br />
                                 <strong>Estimated Download Time:</strong> <span id="dl_time">${testDownload(data.dataset.fileSizeRaw)}</span><br />
                             </div>
                          </div>`;
    }
    return dialogBoxHtml;
}

function testDownload(fileSize) {
    let start = new Date().getTime();
    $.ajax({
        type: "GET",
        url: "/download/testfile.bin?id=" + start,
        success: function(msg) {
            end = new Date().getTime();
            diff = (end - start) / 1000;
            bytes = msg.length;
            speed = (bytes / diff);
            time = filesize / speed;
            unit = "second";
            if (time > 60) {
                time = time / 60;
                unit = "minute";
            }
            if (time > 60) {
                time = time / 60;
                unit = "hour";
            }
            if (Math.round(time) != 1) unit += "s";
            $('#dl_time').html(Math.round(time) + " " + unit + " (based on your current connection speed)");
            if (filesize > 5000000000 && unit == "hours" && time >= 24) {
                showDialog('Notice:', 'This dataset will take approximately ' + Math.round(time) +
                    ' hours to download. Please contact GRIIDC (<a href=mailto:griidc@gomri.org>griidc@gomri.org</a>) ' +
                    ' if you would like to arrange alternative data delivery.');
            }
        },
        error: function () {
            $("#dl_time").html("Failed to calculate");
        }
    });
}
