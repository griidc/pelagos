var $ = jQuery.noConflict();

$(document).ready(function() {
    $('#left').height(0);
    $('#right').height(0);
    setTimeout(function() {
        resizeLeftRight();
        $('#tabs .tab').height($('#tabs').height() - $('#tabs .ui-tabs-nav').height() - 5);
        $('#tabs .tab').each(function() { $(this).tinyscrollbar_update('relative'); });
        if($.cookie("expanded") == 1) {
            expand();
        }
    }, 500);
    $(window).resize(function() {
        resizeLeftRight()
        $('#menu').tinyscrollbar_update('relative');
        $('#tabs .tab').height($('#tabs').height() - $('#tabs .ui-tabs-nav').height() - 5);
        $('#tabs .tab').each(function() { $(this).tinyscrollbar_update('relative'); });
    });
    $('#menu').tinyscrollbar();
    $('#menu .overview').mutate('height', function(el,info) {
        $('#menu').tinyscrollbar_update('relative');
    });

    $('.thumb').mousedown(function() {
        $('body').addClass('noselect');
        document.getElementById('container').setAttribute('onselectstart','return false;');
    });

    $(window).mouseup(function() {
        $('body').removeClass('noselect');
        document.getElementById('container').setAttribute('onselectstart','');
    });

    $('#filter-input').bind('keypress', function(e) {
        if(e.keyCode==13){
            applyFilter();
        }
    });

    $("#expand-collapse").click(function(){
        if ($('#expand-collapse div').hasClass('collapsed')) {
            expand();
        }
        else {
            collapse();
        }
    });
});

function expand() {
    $('#right').animate({'left' : "40%", 'width' : "60%"}, {duration: 'slow'});
    $('#menu').tinyscrollbar_update('relative');
    $('#left').animate({'width' : "40%"}, {duration: 'slow', complete: function() {
        $('#expand-collapse div').removeClass('collapsed');
        $('#tabs .tab').each(function() { $(this).tinyscrollbar_update('relative'); });
    }});
    $.cookie("expanded", 1);
}

function collapse() {
    $('#right').animate({'left' : "0%", 'width' : "100%"}, {duration: 'slow'});
    $('#left').animate({'width' : "0%"}, {duration: 'slow', complete: function() {
        $('#expand-collapse div').addClass('collapsed');
        $('#tabs .tab').each(function() { $(this).tinyscrollbar_update('relative'); });
    }});
    $.cookie("expanded", 0);
}

function resizeLeftRight() {
    $('#left').height(0);
    $('#right').height(0);
    h = $('#main').height() - $('#squeeze-wrapper').height() - 20;
    $('#left').height(h);
    $('#right').height(h);
}

function showDatasets(by,id,peopleId) {
    currentlink = $('#packageLink').attr('href');
    if (currentlink) {
        newlink = currentlink.replace(/\?filter=[^&]*(&|$)/,'');
        if ($('#filter-input').val() != '') {
            newlink += '?filter=' + $('#filter-input').val();
        }
        $('#packageLink').attr('href',newlink);
    }
    $('#content').html('<div class="spinner"><div><img src="{{baseUrl}}/includes/images/spinner.gif"></div></div>');
    $('div.spinner').height($('#content').height()-12);
    $.ajax({
        "url": "{{baseUrl}}/datasets/" + jQuery('#filter-input').val() + "/" + by + "/" + id,
        "success": function(data) {
            $('#content').html(data);
            $('#tabs').tabs({
                activate: function(event, ui) {
                    $('#tabs .tab').height($('#tabs').height() - $('#tabs .ui-tabs-nav').height() - 5);
                    $('#tabs .tab').tinyscrollbar();
                    $('#tabs .thumb').mousedown(function() {
                        $('body').addClass('noselect');
                        document.getElementById('container').setAttribute('onselectstart','return false;');
                    });
                }
            }
            );
            $('#tabs .tab').height($('#tabs').height() - $('#tabs .ui-tabs-nav').height() - 5);
            $('#tabs .tab').tinyscrollbar();
            $('#tabs .thumb').mousedown(function() {
                $('body').addClass('noselect');
                document.getElementById('container').setAttribute('onselectstart','return false;');
            });
            setTimeout(function () { jQuery('#tabs .tab').tinyscrollbar_update('relative'); }, 200);
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

function showDatasetDownload(udi) {
    {% if not logged_in %}
        location.href = "/cas?destination=" + escape("{{pageName}}/download_redirect/" + udi + "?final_destination=" + location.pathname);
    {% endif %}
    $.ajax({
        "url": "{{baseUrl}}/download/" + udi,
        "success": function(data) {
            $('#dataset_download_content').html(data);
            $('#dataset_download').show();
        }
    });
}

function applyFilter() {
    trees['tree'].filter=jQuery('#filter-input').val();
    updateTree(trees['tree']);
}

function clearAll() {
    $('#by-input').val('');
    $('#id-input').val('');
    $('#filter-input').val('');
    trees['tree'].selected = null;
    applyFilter();
}
