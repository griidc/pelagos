var countries;

var firstResize = true;

var $ = jQuery.noConflict();

var winWidth = $(window).width();
var winHeight = $(window).height();

var drawn = { dataset_for: false, dataset_type: false, dataset_procedure: false };

var flotConfig;

var overviewSections;

$(document).ready(function() {

    winWidth = $(window).width();
    winHeight = $(window).height();

    $(window).resize(function() {
        var winNewWidth = $(window).width();
        var winNewHeight = $(window).height();
        if (winNewWidth != winWidth || winNewHeight != winHeight) {
            winWidth = winNewWidth;
            winHeight = winNewHeight;
            if (firstResize) {
                firstResize = false;
                return;
            }
        }
    });

    overviewSections = {
        'summary-of-records': {
            colors: [ '#88F', 'green', 'gold' ],
            xaxis: {
                ticks: false,
                min: 0,
                max: 3.7
            },
            legend: {
                noColumns: 3,
                container: $('#summary-of-records-legend')
            },
            bars: {
                show: true,
                fill: true,
                numbers: {
                    show: true,
                    yAlign: function(plot,y) { return plot.getAxes().yaxis.c2p(plot.getAxes().yaxis.p2c(y)+13); },
                    xAlign: function(plot,x) { return x + 0.4; }
                }
            }
        },
        'total-records-over-time': {
            xaxis: { mode: "time" },
            yaxis: { position: 'right' },
            colors: [ '#88F', '#F55', 'orange' ],
            legend: { position: "nw" }
        },
        'dataset-size-ranges': {
            xaxis: {
                ticks: true,
                min: 0,
                max: 3
            },
            bars: {
                show: true,
                fill: true,
                numbers: {
                    show: true,
                    yAlign: function(plot,y) { 
                        if (y < 25) {
                            return plot.getAxes().yaxis.c2p(plot.getAxes().yaxis.p2c(y)-1);
                        } else {
                            return plot.getAxes().yaxis.c2p(plot.getAxes().yaxis.p2c(y)+13)
                        }
                    },
                    xAlign: function(plot,x) { return x + 0.167; }
                }
            },
            legend: {
                noColumns: 6,
                container: $('#dataset-size-ranges-legend')
            }
        }
    };

    if (page == 'overview') {
        if (type == 'total-records-over-time') {
            $.getJSON(base_url + '/data/overview/total-records-over-time', function(data) {
                $("#total-records-over-time").css('min-height', $("#total-records-over-time").parent().height());
                $.plot($("#total-records-over-time"), data, flotConfig['total-records-over-time']);
                $("#total-records-over-time").css('min-height','');
            });
        }
    }

    else {

    for (section in overviewSections) {
        $.getJSON(base_url + '/data/overview/' + section, function(data) {
            $('#' + data.section).css('min-height', $('#' + data.section).parent().height());
            $.plot($('#' + data.section), data.data, overviewSections[data.section]);
            $('#' + data.section).css('min-height','');
        });
    }

    }

});

function labelFormatter(label, series) {
    return "<div style='font-size:8pt; text-align:center; padding:2px; padding-left:5px; color:#555; background-color:transparent;'>" + label + "<br/>" + series.data[0][1] + " TB (" + Math.round(series.percent) + "%)</div>";
}
