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
                    $("#tree").jstree("open_node", $('#projects_fundSrc_' + m[1]));
                    $("#tree").jstree("select_node", $('#datasets_projectId_' + m[2]), true);
                    $("#tree").jstree("select_node", $('#tasks_projectId_' + m[2]), true);
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
                    event: "mouseleave blur",
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
