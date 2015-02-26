var $ = jQuery.noConflict();

var flotConfig;

var overviewSections;

$(document).ready(function() {

    overviewSections = {
        'summary-of-records': {
            colors: [ '#88F', 'green', 'gold' ],
            xaxis: {
                ticks: false,
                min: 0,
                max: 3
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
                    yAlign: function(plot,y) { return plot.getAxes().yaxis.c2p(plot.getAxes().yaxis.p2c(y)+15); },
                    xAlign: function(plot,x) { return x + 0.35; }
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
                max: 6
            },
            legend: {
                noColumns: 6,
                container: $('#dataset-size-ranges-legend')
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
                    xAlign: function(plot,x) { return x + 0.4; }
                }
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
