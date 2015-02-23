google.load('visualization', '1', {'packages': ['geochart']});

var countries;

var firstResize = true;

function drawRegionsMap() {

    if (typeof countries === 'undefined') return;

    var data = google.visualization.arrayToDataTable(countries);

    if (typeof data === 'undefined' || data === null) return;

    var options = {
        displayMode: 'regions',
        colorAxis: {
            colors: ['#AAF','#88F']
        }
    };

    var chart = new google.visualization.GeoChart(document.getElementById('researcher_map'));
    chart.draw(data, options);

};

var $ = jQuery.noConflict();

var winWidth = $(window).width();
var winHeight = $(window).height();

var drawn = { dataset_for: false, dataset_type: false, dataset_procedure: false };

var resizeTimeout;

var flotConfig;

var overviewSections;

$(document).ready(function() {

    winWidth = $(window).width();
    winHeight = $(window).height();

    $(window).resize(function() {
        var onResize = function() {
             console.log('resize!');
             $('#researcher_map').html('');
             drawRegionsMap();
        }

        var winNewWidth = $(window).width();
        var winNewHeight = $(window).height();
        if (winNewWidth != winWidth || winNewHeight != winHeight) {
            winWidth = winNewWidth;
            winHeight = winNewHeight;
            if (firstResize) {
                firstResize = false;
                return;
            }
            if (typeof resizeTimeout !== 'undefined') window.clearTimeout(resizeTimeout);
            resizeTimeout = window.setTimeout(onResize, 500);
        }
    });

    overviewSections = {
        'summary-of-records': {
            colors: [ '#88F', '#F55', 'orange', 'green' ],
            xaxis: {
                ticks: false,
                min: 0,
                max: 3.7
            },
            legend: {
                noColumns: 4,
                container: $('#summary-of-records-legend')
            },
            bars: {
                show: true,
                fill: true,
                numbers: {
                    show: true,
                    yAlign: function(plot,y) { return plot.getAxes().yaxis.c2p(plot.getAxes().yaxis.p2c(y)-2); },
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
        'dataset-size-ranges': pie_conf,
        'system-capacity': {
            series: {
                pie: {
                    show: true,
                    stroke: false,
                    radius: 1,
                    label: {
                        show: true,
                        radius: .6,
                        formatter: labelFormatter,
                        background: {
                            opacity: 0
                        }
                    }
                }
            },
            colors: [ '#f6d493', '#c6c8f9' ],
            legend: { show: false }
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

    $( "#stats-tabs" ).tabs({
        heightStyleType: "fill",
        activate: function(event,ui) {
            if (!drawn.dataset_for) {
                draw_categories('dataset_for');
                drawn.dataset_for = true;
            }
        }
    });

    $( "#cattabs" ).tabs({
        heightStyleType: "fill",
        activate: function(event,ui) {
            if (ui.newTab.index() == 1 && !drawn.dataset_type) {
                draw_categories('dataset_type');
                drawn.dataset_type = true;
            }
            else if (ui.newTab.index() == 2 && !drawn.dataset_procedure) {
                draw_categories('dataset_procedure');
                drawn.dataset_procedure = true;
            }
        }
    });

    for (section in overviewSections) {
        $.getJSON(base_url + '/data/overview/' + section, function(data) {
            $('#' + data.section).css('min-height', $('#' + data.section).parent().height());
            $.plot($('#' + data.section), data.data, overviewSections[data.section]);
            $('#' + data.section).css('min-height','');
        });
    }

    $.getJSON(base_url + '/data/overview/researcher_map', function(data) {
        countries = data;
        drawRegionsMap();
    });

    }

});

function labelFormatter(label, series) {
    return "<div style='font-size:8pt; text-align:center; padding:2px; padding-left:5px; color:#555; background-color:transparent;'>" + label + "<br/>" + series.data[0][1] + " TB (" + Math.round(series.percent) + "%)</div>";
}

function draw_categories(category_type) {
    for (category in categories[category_type].categories) {
        $.getJSON(base_url + '/data/categories/' + category_type + '/' + category, function(data) {
            var graph_id = categories[data.category_type].categories[data.category].id;
            $('#' + graph_id).css('min-height', $('#' + graph_id).parent().height());
            $.plot('#' + categories[data.category_type].categories[data.category].id, data.data, pie_conf);
            $('#' + graph_id).css('min-height','');
        });
    }
}
