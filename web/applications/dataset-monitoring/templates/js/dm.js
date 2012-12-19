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
    $('.thumb').mousedown(function() {
        $('body').addClass('noselect');
        $('#container').prop('onselectstart','return false;');
    });
    $(window).mouseup(function() {
        $('body').removeClass('noselect');
        $('#container').prop('onselectstart','');
    });
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
            $('#content .overview img[title]').qtip({
                position: {
                    my: 'middle right',
                    at: 'middle left',
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
            setTimeout(function () { jQuery('#content').tinyscrollbar_update('relative'); }, 200);
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
