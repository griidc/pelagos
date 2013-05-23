var $ = jQuery.noConflict();

$(document).ready(function() {
    $('#menu .overview').width($('#menu .viewport').width() - 15);
    $('#left').height(0);
    $('#right').height(0);
    setTimeout(function() { resizeLeftRight(); }, 500);
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
            $('#right').animate({'left' : "40%", 'width' : "60%"}, {duration: 'slow'});
            $('#menu').tinyscrollbar_update('relative');
            $('#left').animate({'width' : "40%"}, {duration: 'slow', complete: function() {
                $('#expand-collapse div').removeClass('collapsed');
                $('#content').tinyscrollbar_update('relative');
            }});
        }
        else {
            $('#right').animate({'left' : "0%", 'width' : "100%"}, {duration: 'slow'});
            $('#left').animate({'width' : "0%"}, {duration: 'slow', complete: function() {
                $('#expand-collapse div').addClass('collapsed');
                $('#content').tinyscrollbar_update('relative');
            }});
        }
    });
});

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
    $('#content .overview').html('<div class="spinner"><div><img src="{{baseUrl}}/includes/images/spinner.gif"></div></div>');
    $('div.spinner').height($('#content .viewport').height()-12);
    $('#content').tinyscrollbar_update('relative');
    $.ajax({
        "url": "{{baseUrl}}/datasets/" + jQuery('#filter-input').val() + "/" + by + "/" + id,
        "success": function(data) {
            $('#content .overview').html(data);
            setTimeout(function () { jQuery('#content').tinyscrollbar_update('relative'); }, 200);
            $.ajax({
                "url": "{{baseUrl}}/package/items",
                "success": function(data) {
                    for (i in data.items) {
                        $('input[id="' + data.items[i] + '_checkbox"]').attr('checked', 'checked');
                    }
                    $('#package-count').html(data.count);
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
