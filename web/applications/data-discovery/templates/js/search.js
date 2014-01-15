var datasets = new Array();

var $ = jQuery.noConflict();

$(document).ready(function() {
    $('#left').height(0);
    $('#right').height(0);
    setTimeout(function() {
        resizeLeftRight();
        $('#map_pane').height(($('#left').height()-5)/2);
        $('#menu').height($('#left').height()-$('#map_pane').height()-10);
        $('#tabs .tab').height($('#tabs').height() - $('#tabs .ui-tabs-nav').height() - 5);
        $('#tabs .tab').each(function() { $(this).tinyscrollbar_update('relative'); });
        if (typeof($.cookie) == 'function' && $.cookie("expanded") == 1) {
            expand();
        }
        initMap('olmap',{'onlyOneFeature':false,'allowModify':false,'allowDelete':true,'labelAttr':'udi'});
        $(document).on('overFeature',function(e,eventVariables) {
            $('table.datasets tr[udi="' + eventVariables.attributes.udi + '"] td').addClass('highlight');
        });
        $(document).on('outFeature',function(e,eventVariables) {
            $('table.datasets tr[udi="' + eventVariables.attributes.udi + '"] td').removeClass('highlight');
        });
        $('#content').html('<div class="spinner"><div><img src="{{baseUrl}}/includes/images/spinner.gif"></div></div>');
    }, 500);
    $(window).resize(function() {
        resizeLeftRight()
        $('#map_pane').height(($('#left').height()-5)/2);
        $('#menu').height($('#left').height()-$('#map_pane').height()-10);
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

    $('#map_pane').mouseleave(function() {
        unhighlightAll();
    });

    $(document).on('filterDrawn',function() {
        console.log('DRAWN!');
        $('#drawGeoFilterButton').removeAttr("disabled");
        $('body').css('cursor','');
        $('#olmap').css('cursor','');
        $('input').css('cursor','');
        console.log(getFilter());
        trees['tree'].geo_filter=getFilter();
        applyFilter();
        $('#clearGeoFilterButton').removeAttr('disabled');
    });
});

function expand() {
    $('#right').animate({'left' : "40%", 'width' : "60%"}, {duration: 'slow'});
    $('#menu').tinyscrollbar_update('relative');
    $('#left').animate({'width' : "40%"}, {duration: 'slow', complete: function() {
        $('#expand-collapse div').removeClass('collapsed');
        $('#tabs .tab').each(function() { $(this).tinyscrollbar_update('relative'); });
    }});
    if (typeof($.cookie) == 'function') $.cookie("expanded", 1);
}

function collapse() {
    $('#right').animate({'left' : "0%", 'width' : "100%"}, {duration: 'slow'});
    $('#left').animate({'width' : "0%"}, {duration: 'slow', complete: function() {
        $('#expand-collapse div').addClass('collapsed');
        $('#tabs .tab').each(function() { $(this).tinyscrollbar_update('relative'); });
    }});
    if (typeof($.cookie) == 'function') $.cookie("expanded", 0);
}

function resizeLeftRight() {
    $('#left').height(0);
    $('#right').height(0);
    h = $('#main').height() - $('#squeeze-wrapper').height() - 20;
    $('#left').height(h);
    $('#right').height(h);
}

function showDatasets(by,id,peopleId) {
    removeAllFeaturesFromMap();
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
    geo_filter = '';
    if (trees['tree'].geo_filter) {
        geo_filter = trees['tree'].geo_filter;
    }
    $.ajax({
        "url": "{{baseUrl}}/datasets/" + encodeURIComponent(jQuery('#filter-input').val().replace(/\//g,"")) + "/" + by + "/" + id + "/" + geo_filter,
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
                    if ($('#showAllFeatures').attr('checked')) {
                        var selectedTab = $("#tabs").tabs('option','active');
                        removeAllFeaturesFromMap();
                        if (datasets[selectedTab]) {
                            for (var i=0; i<datasets[selectedTab].length; i++) {
                                addFeatureFromWKT(datasets[selectedTab][i].geom,{'udi':datasets[selectedTab][i].udi});
                            }
                        }
                    }
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
    if ($('tr[udi="' + udi + '"] td.info').has("div.details:empty").length == 1) {
        $.ajax({
            "url": "{{baseUrl}}/dataset_details/" + udi,
            "success": function(data) {
                $('tr[udi="' + udi + '"] td.info div.details').html(data);
                $('tr[udi="' + udi + '"] td.info div.details').show();
                $('tr[udi="' + udi + '"] td.info div.attributes a.details_link').html('Hide Details');
            }
        });
    }
    else {
        if ($('tr[udi="' + udi + '"] td.info div.details:visible').length == 1) {
            $('tr[udi="' + udi + '"] td.info div.details').hide();
            $('tr[udi="' + udi + '"] td.info div.attributes a.details_link').html('Show Details');
        }
        else {
            $('tr[udi="' + udi + '"] td.info div.details').show();
            $('tr[udi="' + udi + '"] td.info div.attributes a.details_link').html('Hide Details');
        }
    }
}

function showDatasetDownload(udi) {
    {% if not logged_in %}
        $.cookie('dl_attempt_udi_cookie', udi, { expires: 1, path: '/', domain: '{{hostname}}' });
        //location.href = "/cas?destination=" + escape("{{pageName}}/download_redirect/" + udi + "?final_destination=" + location.pathname);
        showLoginOptions(udi);
    {% else %}
        $.ajax({
            "url": "{{baseUrl}}/download/" + udi,
            "success": function(data) {
                $('#dataset_download_content').html(data);
                $  ('#dataset_download').show();
            }
        });
    {% endif %}
}

function applyFilter() {
    removeAllFeaturesFromMap();
    $('#content').html('<div class="spinner"><div><img src="{{baseUrl}}/includes/images/spinner.gif"></div></div>');
    trees['tree'].filter=jQuery('#filter-input').val();
    updateTree(trees['tree']);
}

function clearAll() {
    goHome();
    $('#by-input').val('');
    $('#id-input').val('');
    $('#filter-input').val('');
    trees['tree'].selected = null;
    clearFilter();
    trees['tree'].geo_filter = null;
    applyFilter();
}

function showAllFeatures() {
    if ($('#showAllFeatures').attr('checked')) {
        var selectedTab = $("#tabs").tabs('option','active');
        removeAllFeaturesFromMap();
        if (datasets[selectedTab]) {
            for (var i=0; i<datasets[selectedTab].length; i++) {
                addFeatureFromWKT(datasets[selectedTab][i].geom,{'udi':datasets[selectedTab][i].udi});
            }
        }
    }
    else {
        removeAllFeaturesFromMap();
    }
}
