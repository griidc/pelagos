var datasets = new Array();

var $ = jQuery.noConflict();

var myGeoViz = new GeoViz();

$(document).ready(function() {
    if (typeof($.cookie) == 'function' && $.cookie("expanded") == 1) {
        expand();
    }

    myGeoViz.initMap('olmap',{'onlyOneFeature':false,'allowModify':false,'allowDelete':true,'labelAttr':'udi'});

    $(document).on('overFeature',function(e,eventVariables) {
        $('table.datasets tr[udi="' + eventVariables.attributes.udi + '"] td').addClass('highlight');
    });
    $(document).on('outFeature',function(e,eventVariables) {
        $('table.datasets tr[udi="' + eventVariables.attributes.udi + '"] td').removeClass('highlight');
    });

    $('#filter-input').bind('keypress', function(e) {
        if(e.keyCode==13){
            applyFilter();
        }
    });

    $('#filter-input').focusout(function() {
        if ($('#filter-applied').val() != $('#filter-input').val()) {
            applyFilter();
        }
    });

    $("#expand-collapse").click(function(){
        if ($('#expand-collapse').hasClass('collapsed')) {
            expand();
        }
        else {
            collapse();
        }
    });

    $('#map_pane').mouseleave(function() {
        myGeoViz.unhighlightAll();
    });

    $(document).on('filterDrawn',function() {
        $('body').css('cursor','');
        $('#olmap').css('cursor','');
        $('input').css('cursor','');
        trees['tree'].geo_filter=myGeoViz.getFilter();
        applyFilter();
        $('#clearGeoFilterButton').button('disable');
    });

    $("#show_all_extents_checkbox").button();
    $(".map_button").button();
    $("#filter-button").button();
    $("#clear-button").button();
});

function expand() {
    $('#left').show();
    $('#right').animate({'left' : "45%", 'width' : "55%"}, {duration: 'slow'});
    $('#left').animate({'width' : "45%"}, {duration: 'slow', complete: function() {
        $('#expand-collapse').removeClass('collapsed');
        $('.right-panel').removeClass('right-panel-collapsed');
    }});
    if (typeof($.cookie) == 'function') $.cookie("expanded", 1);
}

function collapse() {
    $('#right').animate({'left' : "0%", 'width' : "100%"}, {duration: 'slow'});
    $('#left').animate({'width' : "0%"}, {duration: 'slow', complete: function() {
        $('#expand-collapse').addClass('collapsed');
        $('#left').hide();
        $('.right-panel').addClass('right-panel-collapsed');
    }});
    if (typeof($.cookie) == 'function') $.cookie("expanded", 0);
}

function resizeLeftRight() {
    $('#left').height(0);
    $('#right').height(0);
    rh = $('#main').height() - $('#filter').height() - $('.tabs').height() - 15;
    lh = $('#main').height() - $('.tabs').height() - 15;
    $('#left').height(lh);
    $('#right').height(rh);
}

function showDatasets(by,id) {
    myGeoViz.removeAllFeaturesFromMap();
    $('#filter-button').button('disable');
    $('#clear-button').button('disable');
    $('#drawGeoFilterButton').button('disable');
    currentlink = $('#packageLink').attr('href');
    if (currentlink) {
        newlink = currentlink.replace(/\?filter=[^&]*(&|$)/,'');
        if ($('#filter-applied').val() != '') {
            newlink += '?filter=' + $('#filter-applied').val();
        }
        $('#packageLink').attr('href',newlink);
    }
    geo_filter = '';
    if (trees['tree'].geo_filter) {
        geo_filter = trees['tree'].geo_filter;
    }
    $.ajax({
        "url": Routing.generate('pelagos_app_ui_datadiscovery_datasets'),
        "data": {
            "filter": jQuery('#filter-applied').val().replace(/\//g,""),
            "by": by,
            "id": id,
            "geo_filter": geo_filter
        },
        "success": function(data) {
            $('#dataset_listing_wrapper .spinner').hide();
            $('#dataset_listing').html(data);
            $('#tabs').tabs({
                activate: function(event, ui) {
                    if ($('#show_all_extents_checkbox').is(':checked')) {
                        var selectedTab = $("#tabs").tabs('option','active');
                        myGeoViz.removeAllFeaturesFromMap();
                        if (datasets[selectedTab]) {
                            for (var i=0; i<datasets[selectedTab].length; i++) {
                                myGeoViz.addFeatureFromWKT(datasets[selectedTab][i].geom,{'udi':datasets[selectedTab][i].udi});
                            }
                        }
                    }
                }
            }
            );
            $('#filter-button').button('enable');
            $('#clear-button').button('enable');
            $('#drawGeoFilterButton').button("enable");
            if (myGeoViz.getFilter()) {
                $('#clearGeoFilterButton').button('enable');
            }
        },
        "error": function(jqXHR, textStatus, errorThrown) {
            alert("Fail: " + textStatus + " " + errorThrown + jqXHR.getResponseHeader());
        }
    });
}

function showDatasetDetails(id) {
    if ($('tr[datasetId="' + id + '"] td.info').has("div.details:empty").length == 1) {
        $.ajax({
            "url": Routing.generate("pelagos_app_ui_datadiscovery_showdetails", { "id": id }),
            "success": function(data) {
                $('tr[datasetId="' + id + '"] td.info div.details').html(data);
                $('tr[datasetId="' + id + '"] td.info div.details').show();
                $('tr[datasetId="' + id + '"] td.info div.attributes a.details_link').html('Hide Details');
            }
        });
    }
    else {
        if ($('tr[datasetId="' + id + '"] td.info div.details:visible').length == 1) {
            $('tr[datasetId="' + id + '"] td.info div.details').hide();
            $('tr[datasetId="' + id + '"] td.info div.attributes a.details_link').html('Show Details');
        }
        else {
            $('tr[datasetId="' + id + '"] td.info div.details').show();
            $('tr[datasetId="' + id + '"] td.info div.attributes a.details_link').html('Hide Details');
        }
    }
}

function showDatasetDownload(udi) {
//    {% if not logged_in %}
        $.cookie('dl_attempt_udi_cookie', udi, { expires: 1, path: '/', domain: '{{hostname}}' });
        $('#pre_login').show();
/*
    {% else %}
        $.ajax({
            "url": "{{baseUrl}}/download/" + udi,
            "success": function(data) {
                $('#dataset_download_content').html(data);
                $('#dataset_download').show();
                $(".dl_button[title]").qtip({
                    position: {
                        viewport: $(window),
                        my: 'bottom left',  // position of arrow
                        at: 'top right'     // position relative to selector
                    },
                    style: {
                        classes: "qtip-shadow qtip-tipped customqtip"
                    }
                });
            }
        });
    {% endif %}
*/
}

function showGridFTPDetails(udi) {
    $(".qtip").hide();
    $.ajax({
            "url": "{{baseUrl}}/enableGridFTP/" + udi,
            "success": function(data) {
                $('#dataset_download_content').html(data);
            }
    });
}

function showWebDownloadDetails(udi) {
    $(".qtip").hide();
    $.ajax({
            "url": "{{baseUrl}}/initiateWebDownload/" + udi,
            "success": function(data) {
                $('#dataset_download_content').html(data);
            }
    });
}

function showDatasetDownloadExternal(udi) {
    $(".qtip").hide();
    $.ajax({
            "url": "{{baseUrl}}/download-external/" + udi,
            "success": function(data) {
                $('#dataset_download_content').html(data);
                $('#dataset_download').show();
            }
    });
}

function applyFilter() {
    $('#filter-button').button('disable');
    $('#clear-button').button('disable');
    $('#drawGeoFilterButton').button('disable');
    myGeoViz.removeAllFeaturesFromMap();
    $('#dataset_listing').html("");
    $('#dataset_listing_wrapper .spinner').show();
    trees['tree'].filter=jQuery('#filter-input').val();
    jQuery('#filter-applied').val(jQuery('#filter-input').val());
    updateTree(trees['tree']);
}

function clearAll() {
    myGeoViz.goHome();
    $('#by-input').val('');
    $('#id-input').val('');
    $('#filter-input').val('');
    $('#filter-applied').val('');
    trees['tree'].selected = null;
    myGeoViz.clearFilter();
    $('#clearGeoFilterButton').button('disable');
    trees['tree'].geo_filter = null;
    applyFilter();
}

function showAllExtents() {
    if ($('#show_all_extents_checkbox').is(':checked')) {
        $('#show_all_extents_label').html('<span class="ui-button-text">Hide All Extents</span>');
        var selectedTab = $("#tabs").tabs('option','active');
        myGeoViz.removeAllFeaturesFromMap();
        if (datasets[selectedTab]) {
            for (var i=0; i<datasets[selectedTab].length; i++) {
                myGeoViz.addFeatureFromWKT(datasets[selectedTab][i].geom,{'udi':datasets[selectedTab][i].udi});
            }
        }
    }
    else {
        $('#show_all_extents_label').html('<span class="ui-button-text">Show All Extents</span>');
        $('table.datasets tr td').removeClass('highlight');
        myGeoViz.removeAllFeaturesFromMap();
    }
}

function addTree() {
    insertTree({
        start: "ra",
        title: "Filter by Research Award",
        theme: "classic",
        max_depth: 2,
        expand_to_depth: 0,
        include_datasets: "identified",
        animation: 250,
        filter: "",
        onload: "if (!tree.selected) { showDatasets($('#by-input').val(),$('#id-input').val(),''); } else if ($('#' + tree.name).jstree('get_selected').length < 1) { showDatasets($('#by-input').val(),$('#id-input').val(),''); }",
        on_filter_by_change: "$('#by-input').val('');$('#id-input').val('');",

        rfp_color: "#00A",
        rfp_action: "$('#by-input').val('fundSrc'); $('#id-input').val('\{\{fundSrc.ID\}\}'); showDatasets('fundSrc',\{\{fundSrc.ID\}\});",

        project_color: "#00A",
        project_action: "$('#by-input').val('projectId'); $('#id-input').val('\{\{project.ID\}\}'); showDatasets('projectId',\{\{project.ID\}\});",

        deselect_action: "$('#by-input').val(''); $('#id-input').val(''); showDatasets('','');"
    });
}
