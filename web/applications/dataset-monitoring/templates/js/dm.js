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
    $('#left').height(0);
    $('#right').height(0);
    setTimeout(function() { resizeLeftRight(); }, 100);
    $(window).resize(function() {
        resizeLeftRight()
        $('#menu').tinyscrollbar_update('relative');
        $('#content').tinyscrollbar_update('relative');
    });
    $('#menu').tinyscrollbar();
    $('#content').tinyscrollbar();
    $('#menu .overview').mutate('height', function(el,info) {
        $('#menu').tinyscrollbar_update('relative');
    });
    $('#content .overview').mutate('height', function(el,info) {
        $('#content').tinyscrollbar_update('relative');
    });
    $('.thumb').mousedown(function() {
        $('body').addClass('noselect');
        document.getElementById('container').setAttribute('onselectstart','return false;');
    });

    $(window).mouseup(function() {
        $('body').removeClass('noselect');
        document.getElementById('container').setAttribute('onselectstart','');
    });

    $(window).hashchange( function(){
        var m = location.hash.match(/^#([^\/]+)\/?([^\/]+)?/);
        if (m) {
            if (typeof m[1] !== 'undefined') {
                if (typeof m[2] === 'undefined') {
                    $("#tree").jstree("select_node", $('#datasets_projectId_' + m[1]), true);
                    $("#tree").jstree("select_node", $('#tasks_projectId_' + m[1]), true);
                }
                else {
                    $("#tree").jstree("open_node", $('#projects_fundSrc_' + map_fund_src(m[1])));
                    $("#tree").jstree("select_node", $('#datasets_projectId_' + map_project(m[2])), true);
                    $("#tree").jstree("select_node", $('#tasks_projectId_' + map_project(m[2])), true);
                }
            }
        }
    })

});

function resizeLeftRight() {
    $('#left').height(0);
    $('#right').height(0);
    h = $('#main').height() - $('#squeeze-wrapper').height() - 20;
    $('#left').height(h);
    $('#right').height(h);
}

function showProjects(by,id) {
    $('#content .overview').html('<div class="spinner"><div><img src="{{baseUrl}}/includes/images/spinner.gif"></div></div>');
    $('div.spinner').height($('#content .viewport').height()-12);
    $('#content').tinyscrollbar_update('relative');
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
                    classes: "ui-tooltip-shadow ui-tooltip-tipped"
                }
            });
        }
    });
}

function showDatasetDetails(udi) {
    $.ajax({
        "url": "{{baseUrl}}/dataset_details/" + udi,
        "success": function(data) {
            $('#dataset_details_content').html(data);
            $('#dataset_details').show();
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
