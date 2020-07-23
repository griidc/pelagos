const $ = require('jquery');
global.$ = global.jQuery = $;

import '../css/stats.css';

import 'bootstrap';

var flotConfig;
var overviewSections;
var style = getComputedStyle(document.body);
var theme = {};

theme.main = style.getPropertyValue('--color-main');
theme.secondary = style.getPropertyValue('--color-menu');
theme.dark = style.getPropertyValue('--color-headerMiddle');
theme.light = style.getPropertyValue('--color-headerTop');

$(document).ready(function() {
    overviewSections = {
        "total-records-over-time": {
            url: Routing.generate("pelagos_app_ui_stats_getdatasetovertime"),
            xaxis: { mode: "time" },
            yaxis: { position: "right" },
            colors: [ theme.secondary, theme.light, theme.main ],
            legend: { position: "nw" }
        },
        "dataset-size-ranges": {
            url: Routing.generate("pelagos_app_ui_stats_getdatasetsizeranges"),
            colors: [ theme.dark, theme.dark, theme.dark, theme.dark, theme.dark, theme.dark ],
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
