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
    updateTree('ra');
});

function resizeLeftRight() {
    $('#left').height(0);
    $('#right').height(0);
    h = $('#main').height() - $('#squeeze-wrapper').height() - 20;
    $('#left').height(h);
    $('#right').height(h);
}

function updateTree(type) {
    $("#tree").jstree({
        "core": {
            "html_titles": true
        },
        "themes": {
            "theme": "classic",
            "dots": true,
            "icons": false
        },
        "json_data": {
            "ajax": {
                "url": function (node) {
                    var nodeId = "";
                    var url = "";
                    if (node == -1) {
                        url = "{{baseUrl}}/json/"+type+".json";
                    }
                    else {
                        nodeId = node.attr('id');
                        url = "{{baseUrl}}/json/"+type+"/"+nodeId+".json";
                    }
                    return url;
                },
                "success": function (new_data) {
                    setTimeout(function() { $('#menu').tinyscrollbar_update('relative'); }, 500);
                    return new_data;
                }
            }
        },
        "plugins": [ "json_data", "types", "themes" ]
    });
    $.vakata.css.add_sheet({ str : '.jstree a { height: auto; }', title : "jstree_override" });
}

function showProjects(by,id) {
    $('#content .overview').html('<div class="spinner"><div><img src="{{baseUrl}}/includes/images/spinner.gif"></div></div>');
    $('div.spinner').height($('#content .viewport').height()-12);
    $('#content').tinyscrollbar_update('relative');
    $.ajax({
        "url": "{{baseUrl}}/projects/" + by + "/" + id,
        "success": function(data) {
            $('#content .overview').html(data);
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
