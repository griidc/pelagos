
$(document).ready(function() {
    // If cookie is set and we are logged in (per php variable as a literal in js) remove it and initiate download
    /*
    if ((<?php if ($logged_in) { print "1"; } else { print "0";} ?>) && (typeof $.cookie('dl_attempt_udi_cookie') != 'undefined')) {
        var dl_cookie = $.cookie('dl_attempt_udi_cookie');
        $.cookie("dl_attempt_udi_cookie", null, { path: "/", domain: "<?php print "$server_name"; ?>" });
        if (dl_cookie != null) {
            showDatasetDownload(dl_cookie);
        }
    }
    */
});

var dlmap = new GeoViz();

(function ($) {
    $(function() {

        var udi = $('#udi').html();

        resizeMap();

        $( window ).resize(function()
        {
            resizeMap();
        });

        $("#rawxml").width($(document).width()*.90);

//        if ( 1 > 0) { // <?php echo $publinkCount ?>
//            $("#tabs").tabs({ heightStyle: "content" });
//        } else {
            $("#tabs").tabs({ heightStyle: "content", disabled: [ 2 ] });
//        }

        $("#xmlradio").buttonset();

        $("#xmlraw").click(function() {
            $("#formatedxml").hide();
            $("#rawxml").show();
        });

        $("#xmlformated").click(function() {
            $("#formatedxml").show();
            $("#rawxml").hide();
        });

        dlmap.initMap('dlolmap',{'onlyOneFeature':false,'allowModify':false,'allowDelete':false,'staticMap':false,'labelAttr':'udi'});

        $("#downloadds").button().click(function() {
            // For now, just attempt to download.
            window.location = Routing.generate("pelagos_app_ui_dataland_download", {"udi": udi});
            /* Should eventually do this:
            if ("dataset_download_status"  == "RemotelyHosted") {
                showDatasetDownloadExternal(udi)
            } else {
                showDatasetDownload(udi)
            }
            */
        });

        $("#download_dialog").dialog({
            autoOpen: false,
            buttons: {
                OK: function() {
                    $(this).dialog("close");
                }
            },
            modal: true,
            resizable:false
        });

        $("#downloaddsden").button().click(function() {
            $("#download_dialog").dialog('option', 'title', 'Dataset Not Available');
            $("#download_dialog").html('This dataset is not available for download.');
            $("#download_dialog").dialog('open');
        });

        $("#metadatadl").button().click(function() {
            window.location = Routing.generate("pelagos_app_ui_dataland_metadata", {"udi": udi});
        });

        $.fn.qtip.defaults = $.extend(true, {}, $.fn.qtip.defaults, {
            show: {
                event: "mouseenter focus",
                solo: true
            },
            hide: {
                event: "mouseleave blur",
                delay: 100,
                fixed: true
            },
            style: {
                classes: "qtip-default qtip-shadow qtip-tipped"
            }
        });

        $("#downloadds").qtip({
            position: {
                adjust: {
                    method: "flip flip"
                },
                my: "bottom right",
                at: "top left",
                viewport: $(window)
            },
            content: {
                text: 'Download Dataset'
            }
        });

        $("#downloaddsden").qtip({
            position: {
                adjust: {
                    method: "flip flip"
                },
                my: "bottom right",
                at: "top left",
                viewport: $(window)
            },
            content: {
                text: 'This dataset is not currently available for download.'
            }
        });

        $("#downloaddsdenmd").qtip({
            position: {
                adjust: {
                    method: "flip flip"
                },
                my: "bottom right",
                at: "top left",
                viewport: $(window)
            },
            content: {
                text: 'This dataset is not currently available for download until its metadata is approved.'
            }
        });

        $("#metadatadl").qtip({
            position: {
                adjust: {
                    method: "flip flip"
                },
                my: "bottom right",
                at: "top left",
                viewport: $(window)
            },
            content: {
                text: 'Download Metadata'
            }
        });

        $("#metadatadl-dis").qtip({
            position: {
                adjust: {
                    method: "flip flip"
                },
                my: "bottom right",
                at: "top left",
                viewport: $(window)
            },
            content: {
                text: 'Metadata will be available after it is approved.'
            }
        });

        $('td[title]').qtip({
            position: {
                my: 'right bottom',
                at: 'middle left',
                adjust: {
                    x: -2
                },
                viewport: $(window)
            },
            show: {
                event: "mouseenter focus",
                solo: true
            },
            hide: {
                fixed: true,
                delay: 100
            }
        });

    });

    function resizeMap()
    {
        $("#dlolmap").width($(document).width()*.40);
        mapscreenhgt = $("#dlolmap").width()/4*3;
        summaryhgt = $("#summary").height()
        if (mapscreenhgt > summaryhgt)
        {
            $("#dlolmap").height(mapscreenhgt)
        }
        else
        {
            $("#dlolmap").height(summaryhgt)
        }
    };

    $(document).on('imready', function(e) {

        var geovizMap =  $(e.target);
        var udi = $('#udi').html();

        if (geovizMap.attr("description") != "" && geovizMap.attr("wkt") == "") {
            var imagePath = geovizMap.attr('labimage');
            dlmap.addImage(imagePath,0.4);
            console.log('lab only')
            dlmap.makeStatic();
        } else if (geovizMap.attr("wkt") != "") { //  add the geometry from the data. Either datasets or metadata
            dlmap.addFeatureFromWKT(geovizMap.attr("wkt"), {"udi":udi});
            dlmap.gotoAllFeatures();
        }
    });
})(jQuery);
