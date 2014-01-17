var datasets = new Array();

var $ = jQuery.noConflict();

$(document).ready(function() {
    setTimeout(function() {
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
        $('#dataset_listing').html('<div class="spinner"><div><img src="{{baseUrl}}/includes/images/spinner.gif"></div></div>');
    }, 500);

    $('#filter-input').bind('keypress', function(e) {
        if(e.keyCode==13){
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
        unhighlightAll();
    });

    $(document).on('filterDrawn',function() {
        console.log('DRAWN!');
        $('#drawGeoFilterButton').button("enable");
        $('body').css('cursor','');
        $('#olmap').css('cursor','');
        $('input').css('cursor','');
        console.log(getFilter());
        trees['tree'].geo_filter=getFilter();
        applyFilter();
        $('#clearGeoFilterButton').button('enable');
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
    $('#dataset_listing').html('<div class="spinner"><div><img src="{{baseUrl}}/includes/images/spinner.gif"></div></div>');
    geo_filter = '';
    if (trees['tree'].geo_filter) {
        geo_filter = trees['tree'].geo_filter;
    }
    $.ajax({
        "url": "{{baseUrl}}/datasets/" + encodeURIComponent(jQuery('#filter-input').val().replace(/\//g,"")) + "/" + by + "/" + id + "/" + geo_filter,
        "success": function(data) {
            $('#dataset_listing').html(data);
            $('#tabs').tabs({
                activate: function(event, ui) {
                    if ($('#show_all_extents_checkbox').attr('checked')) {
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
        },
        "error": function(jqXHR, textStatus, errorThrown) {
            alert("Fail: " + textStatus + " " + errorThrown + jqXHR.getResponseHeader());
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
    $('#dataset_listing').html('<div class="spinner"><div><img src="{{baseUrl}}/includes/images/spinner.gif"></div></div>');
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

function showAllExtents() {
    if ($('#show_all_extents_checkbox').attr('checked')) {
        $('#show_all_extents_label').html('Hide All Extents');
        var selectedTab = $("#tabs").tabs('option','active');
        removeAllFeaturesFromMap();
        if (datasets[selectedTab]) {
            for (var i=0; i<datasets[selectedTab].length; i++) {
                addFeatureFromWKT(datasets[selectedTab][i].geom,{'udi':datasets[selectedTab][i].udi});
            }
        }
    }
    else {
        $('#show_all_extents_label').html('Show All Extents');
        removeAllFeaturesFromMap();
    }
}

function addTree() {
    insertTree({
        label: "Filter by:",
        theme: "classic",
        max_depth: 1,
        expand_to_depth: 0,
        include_datasets: "identified",
        animation: 250,
        type: "ra",
        filter: "",
        onload: "if(!tree.selected){showDatasets($('#by-input').val(),$('#id-input').val(),'');}",
        show_other_sources: true,

        yr1_folder_color: "#00A",
        yr1_folder_action: "$('#by-input').val('YR1'); $('#id-input').val('1'); showDatasets('YR1',1);",

        yr1_color: "#00A",
        yr1_action: "$('#by-input').val('fundSrc'); $('#id-input').val('\{\{fundSrc.ID\}\}'); showDatasets('fundSrc',\{\{fundSrc.ID\}\});",

        rfp_color: "#00A",
        rfp_action: "$('#by-input').val('fundSrc'); $('#id-input').val('\{\{fundSrc.ID\}\}'); showDatasets('fundSrc',\{\{fundSrc.ID\}\});",

        project_color: "#00A",
        project_action: "$('#by-input').val('projectId'); $('#id-input').val('\{\{project.ID\}\}'); showDatasets('projectId',\{\{project.ID\}\},'\{\{peopleId\}\}');",

        task_color: "#00A",
        task_action: "showTask(\{\{task.ID\}\});",

        dataset_color: "#00A",
        dataset_action: "showDataset(\{\{dataset.udi\}\});",

        researcher_color: "#00A",
        researcher_action: "$('#by-input').val('peopleId'); $('#id-input').val('\{\{person.ID\}\}'); showDatasets('peopleId',\{\{person.ID\}\});",

        institution_color: "#00A",
        institution_action: "$('#by-input').val('institutionId'); $('#id-input').val('\{\{institution.ID\}\}'); showDatasets('institutionId',\{\{institution.ID\}\});",

        other_sources_folder_color: "#00A",
        other_sources_folder_action: "$('#by-input').val('otherSources'); $('#id-input').val('1'); showDatasets('otherSources',1);",

        other_sources_color: "#00A",
        other_sources_action: "$('#by-input').val('otherSource'); $('#id-input').val('\{\{source.ID\}\}'); showDatasets('otherSource','\{\{source.ID\}\}');",

        deselect_action: "$('#by-input').val(''); $('#id-input').val(''); showDatasets('','');"
    });
}
