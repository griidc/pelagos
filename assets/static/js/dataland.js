
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

        $("#tabs").tabs({ heightStyle: "content" });

        $('#tabs li[disabled]').each(function () {
           $( "#tabs" ).tabs( "disable", $(this).find("a").attr("href") );
        });

        $(".xmlcheckradio").checkboxradio({
            icon: false
        });

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
            var id = $("[datasetId]").attr("datasetId");
            startDownload(id);
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

        $("#downloaddsdenrestricted").button().click(function() {
            $("#download_dialog").dialog('option', 'title', 'Restricted Access Dataset');
            $("#download_dialog").html('The author has restricted access of this dataset. ' +
                'Please contact the author to request the dataset. Please contact help@griidc.org with any questions.');
            $("#download_dialog").dialog('open');
        });

        $("#metadatadl").button().click(function() {
            window.location = Routing.generate("pelagos_app_ui_dataland_metadata", {"udi": udi});
        });

        $("#erddaplink").button().click(function() {
            window.open($(this).attr("data-link"), "_blank");
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

        $("#downloaddsdenrestricted").qtip({
            position: {
                adjust: {
                    method: "flip flip"
                },
                my: "bottom right",
                at: "top left",
                viewport: $(window)
            },
            content: {
                text: 'Download dataset'
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

        $("#erddaplink").qtip({
            position: {
                adjust: {
                    method: "flip flip"
                },
                my: "top left",
                at: "bottom left",
                viewport: $(window)
            },
            content: {
                text: 'View associated ERDDAP data.'
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
