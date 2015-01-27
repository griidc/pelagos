var fund_src_map = {
    {% for fund in funds %}
        '{{fund.Abbr}}': '{{fund.ID}}',
    {% endfor %}
};

var rev_fund_src_map = {
    {% for fund in funds %}
        '{{fund.ID}}': '{{fund.Abbr}}',
    {% endfor %}
};

var project_map = {
    {% for project in projects %}
        '{{project.Abbr}}': '{{project.ID}}',
    {% endfor %}
}

var rev_project_map = {
    {% for project in projects %}
        '{{project.ID}}': '{{project.Abbr}}',
    {% endfor %}
}

var $ = jQuery.noConflict();

$(document).ready(function() {
    $('#menu .overview').width($('#menu .viewport').width() - 15);

    $(window).hashchange( function(){
        var m = location.hash.match(/^#([^\/]+)\/?([^\/]+)?/);
        if (m) {
            if (typeof m[1] !== 'undefined') {
                if (typeof m[2] === 'undefined') {
                    if ($('#tree').jstree('get_selected').attr('id') != 'datasets_projectId_' + m[1]) {
                        $("#tree").jstree("select_node", $('#datasets_projectId_' + m[1]), true);
                    }
                    if ($('#tree').jstree('get_selected').attr('id') != 'tasks_projectId_' + m[1]) {
                        $("#tree").jstree("select_node", $('#tasks_projectId_' + m[1]), true);
                    }
                }
                else {
                    $("#tree").jstree("open_node", $('#projects_fundSrc_' + map_fund_src(m[1])));
                    if ($('#tree').jstree('get_selected').attr('id') != 'datasets_projectId_' + map_project(m[2])) {
                        $("#tree").jstree("select_node", $('#datasets_projectId_' + map_project(m[2])), true);
                    }
                    if ($('#tree').jstree('get_selected').attr('id') != 'tasks_projectId_' + map_project(m[2])) {
                        $("#tree").jstree("select_node", $('#tasks_projectId_' + map_project(m[2])), true);
                    }
                }
            }
        }
    })
});

function showProjects(by,id) {
    $('#content .overview').html('<div class="spinner"><div><img src="{{baseUrl}}/includes/images/spinner.gif"></div></div>');
    $('div.spinner').height($('#content .viewport').height()-12);
    $.ajax({
        "url": "{{baseUrl}}/projects/" + by + "/" + id,
        "success": function(data) {
            $('#content .overview').html(data);
            $('#content .overview td[title]').qtip({
                position: {
                    my: 'right bottom',
                    at: 'center',
                    adjust: {
                        x: -8
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
                },
                style: {
                    classes: "qtip-default qtip-tipped"
                }
            });
            $('#content .overview th[title]').qtip({
                position: {
                    my: 'bottom center',
                    at: 'top center',
                    viewport: $(window)
                },
                show: {
                    event: "mouseenter focus",
                    solo: true
                },
                hide: {
                    fixed: true,
                    delay: 100
                },
                style: {
                    classes: "qtip-default qtip-tipped"
                }
            });
            $('#content .overview td.details').each(function() {
                $(this).qtip({
                    content: {
                        text: "loading...",
                        ajax: {
                            url: "{{baseUrl}}/dataset_details/" + escape($(this).parent().attr('udi')),
                            loading: false
                        }
                    },
                    position: {
                        my: 'right bottom',
                        at: 'middle left',
                        viewport: $(window),
                        effect: false,
                        target: $(this).parent()
                    },
                    show: {
                        event: "mouseenter focus",
                        solo: true
                    },
                    hide: {
                        fixed: true,
                        delay: 100
                    },
                    style: {
                        classes: "qtip-default qtip-tipped"
                    }
                });
            });
            $('#content .overview table.tablesorter').tablesorter({
                sortList: [[0,0]],
                sortRestart : true,
                sortInitialOrder: 'asc'
            });
            $(".sbarchart").each(function() {
                var projectId = this.parentNode.id;
                /* remove non-numeric part of the passed div ID */
                projectId = projectId.replace(/^.*_/,'');

                $.getJSON( "{{baseUrl}}/summaryCount/" + projectId, null, function( data ) {
                    var jsondata = data[0];
                    var projectId = data[1];
                    var options = {
                        xaxis: {
                            min: 0,
                            minTickSize: 1
                        },
                        yaxis: {
                            ticks: false
                        },
                        series: {
                            bars: {
                                align: "center",
                                show: true,
                                barWidth: .9,
                                horizontal: true
                            },
                            stack: true
                        },
                        grid: {
                            show: true
                        },
                        colors: [ "#6fcd6f", "#eee566", "#bababa" ]
                    };
                    var projectGraph = $("div#sbarchart_" + projectId + " > .sbarchart" );
                    var projectText = $("div#" + "sbarchart_" + projectId + " > .sbarchart-counts");
                    $.plot(projectGraph, jsondata, options);
                    var registered_len = jsondata[1]["data"][0][0];
                    var available_len  = jsondata[0]["data"][0][0];
                    var identified_len = jsondata[2]["data"][0][0];
                    var available = available_len;
                    var registered = registered_len + available_len;
                    var identified = available_len + registered_len + identified_len;
                    projectText.html("<table><tr><td>Available: " + available + " </td><td>Total Registered: " + registered + "</td><td>Identified: " + identified + "</td></tr></table>");
                }).fail(function() {
                    console.log("failed");
                });
            });
        }
    });
}

function updateHash(fund_src,project_id) {
    location.href = '#' + rev_map_fund_src(fund_src) + '/' + rev_map_project(project_id);
}

function map_fund_src(name) {
    if (name in fund_src_map) return fund_src_map[name];
    return name;
}

function rev_map_fund_src(name) {
    if (name in rev_fund_src_map) return rev_fund_src_map[name];
    return name;
}

function map_project(name) {
    if (name in project_map) return project_map[name];
    return name;
}

function rev_map_project(name) {
    if (name in rev_project_map) return rev_project_map[name];
    return name;
}
