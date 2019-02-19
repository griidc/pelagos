var $ = jQuery.noConflict();

var flotConfig;

var overviewSections;

$(document).ready(function() {

    overviewSections = {
        "total-records-over-time": {
            url: Routing.generate("pelagos_app_ui_stats_getdatasetovertime"),
            xaxis: { mode: "time" },
            yaxis: { position: "right" },
            colors: [ "#b8dcf1", "#bfcacd", "#004250" ],
            legend: { position: "nw" }
        },
        "dataset-size-ranges": {
            url: Routing.generate("pelagos_app_ui_stats_getdatasetsizeranges"),
            colors: [ "#3f626a", "#3f626a", "#3f626a", "#3f626a", "#3f626a", "#3f626a" ],
            xaxis: {
                ticks: true,
                min: 0,
                max: 6
            },
            legend: {
                noColumns: 6,
                container: $("#dataset-size-ranges-legend")
            },
            bars: {
                show: true,
                fill: true,
                numbers: {
                    show: true,
                    yAlign: function(plot,y) {
                        if (y <= 100) {
                            return plot.getAxes().yaxis.c2p(plot.getAxes().yaxis.p2c(y)-1);
                        } else {
                            return plot.getAxes().yaxis.c2p(plot.getAxes().yaxis.p2c(y)+15)
                        }
                    },
                    xAlign: function(plot,x) { return x + 0.4; }
                }
            }
        }
    };

    $.each(overviewSections, function(section, sectionData) {
        $.getJSON(sectionData.url, function(data) {
            $("#" + data.section).css("min-height", $("#" + data.section).parent().height());
            $.plot($("#" + data.section), data.data, overviewSections[data.section]);
            $("#" + data.section).css("min-height", "");
        });
    });
});
