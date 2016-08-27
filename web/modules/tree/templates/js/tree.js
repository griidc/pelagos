var $ = jQuery.noConflict();

var trees = {};

var childrenLoading = 0;

function insertTree(tree) {
    if (typeof tree === 'undefined') {
        tree = {};
    }
    if (typeof tree.name === 'undefined') {
        tree.name = 'tree';
    }
    if (typeof tree.label === 'undefined') {
        tree.label = "{{tree.label}}";
    }
    if (typeof tree.type === 'undefined') {
        tree.type = "{{tree.init_type}}";
    }
    if (typeof tree.init_open === 'undefined') {
        tree.init_open = {{tree.init_open | raw}};
    }
    if (typeof tree.animation === 'undefined') {
        tree.animation = {{tree.animation}};
    }
    if (typeof tree.theme === 'undefined') {
        tree.theme = "{{tree.theme}}";
    }
    if (typeof tree.dots === 'undefined') {
        tree.dots = {{tree.dots}};
    }
    if (typeof tree.icons === 'undefined') {
        tree.icons = {{tree.icons}};
    }
    trees[tree.name] = tree;

/*
    var css = document.createElement("link")
    css.setAttribute("rel", "stylesheet")
    css.setAttribute("type", "text/css")
    css.setAttribute("href", "/tree/includes/css/jstree.css")
    document.getElementsByTagName("head")[0].appendChild(css);
*/

    document.write('<div class="treecontainer">');
    document.write('    <div class="treetype-wrapper">');
    document.write('        <span class="treetype">');

    if (typeof tree === 'undefined' || typeof tree.start === 'undefined') {
        document.write('            <strong>' + tree.label + '</strong>');
        var on_filter_by_change = '';
        if (typeof tree.on_filter_by_change !== 'undefined') {
            on_filter_by_change = tree.on_filter_by_change;
        }
        document.write('            <select id="treetype-selector" onchange="' + on_filter_by_change + 'trees[\'' + tree.name + '\'].selected=null;trees[\'' + tree.name + '\'].type=this.value;updateTree(trees[\'' + tree.name + '\']);">');
        document.write('                <option value="ra"');
        if (tree.type == "ra") document.write(' selected');
        document.write('>Research Award</option>');
        document.write('                <option value="re"');
        if (tree.type == "re") document.write(' selected');
        document.write('>Researcher</option>');
        document.write('            </select>');
    }
    else if (typeof tree.title !== 'undefined') {
        document.write('            <strong>' + tree.title + '</strong>');
    }

    document.write('        </span>');
    document.write('    </div>');
    document.write('    <div id="' + tree.name + '"></div>');
    document.write('</div>');

    $(document).ready(function() {
        $.getScript("/includes/jstree/jquery.jstree.js", function (data, textStatus, jqxhr) {
            updateTree(tree);
        });
    });
}

function updateTree(tree) {
    var init_open = [];
    if (tree.type == "ra") {
        for (i in tree.init_open) {
            init_open.push(tree.init_open[i]);
        }
    }
    

    if (tree.selected) {
        selected_node = $("#" + tree.name).jstree('get_selected');
        if (typeof(selected_node) != 'undefined' && typeof(selected_node.attr('id')) != 'undefined' && selected_node.attr('id') != 'tree') {
            selected_node.parents("li").each(function () {
                var this_id = $(this).attr("id");
                if ($.inArray(this_id,init_open) == -1) {
                    init_open.push(this_id);
                }
            });
        }
    }

    var left_to_open = init_open.length;

    $("#" + tree.name).jstree({
        "core": {
            "html_titles": true,
            "initially_open": init_open,
            "animation": 0
        },
        "themes": {
            "theme": tree.theme,
            "url": "/includes/jstree/themes/" + tree.theme + "/style.css",
            "dots": tree.dots,
            "icons": tree.icons
        },
        "json_data": {
            "ajax": {
                "url": function (node) {
                    var nodeId = "";
                    var url = "";
                    if (node == -1) {
                        if (typeof tree.start === 'undefined') {
                            url = "{{baseUrl}}/json/"+tree.type+".json" + "?tree=" + encodeURIComponent(JSON.stringify(tree));
                        }
                        else {
                            url = "{{baseUrl}}/json/"+tree.start+".json" + "?tree=" + encodeURIComponent(JSON.stringify(tree));
                        }
                    }
                    else {
                        nodeId = node.attr('id');
                        nodePath = nodeId.replace(/_/g,"/");
                        url = "{{baseUrl}}/json/"+tree.type+"/"+nodePath+".json" + "?tree=" + encodeURIComponent(JSON.stringify(tree));
                    }
                    return url;
                },
                "success": function (new_data) {
                    return new_data;
                }
            }
        },
        "ui": { "select_limit": 1, "initially_select": [ trees[tree.name].selected ] },
        "plugins": [ "json_data", "types", "themes", "ui" ]
    });

    $("#" + tree.name).bind("after_open.jstree", function(event, data) {
        childrenLoading--;
        loadOpenChildren(data.inst,data.rslt.obj);
        var settings = data.inst._get_settings();
        if (childrenLoading < 1) {
            settings.core.animation = tree.animation;
            if (typeof tree.afteropen !== 'undefined') {
                eval(tree.afteropen);
            }
        }
        if (left_to_open > 0) {
            left_to_open--;
            if (left_to_open == 0) {
                if (typeof tree.onload !== 'undefined') {
                    eval(tree.onload);
                }
            }
        }
    });

    $("#" + tree.name).bind("loaded.jstree", function(event, data) {
        cssUrl = '/tree/includes/css/jstree.css';
        if ($('link[rel*=style][href="' + cssUrl + '"]').length==0) {
            $('head').append('<link rel="stylesheet" type="text/css" media="all" href="' + cssUrl + '" />');
        }
        loadOpenChildren(data.inst,-1);
        var root_nodes=data.inst._get_children(-1);
        var root_node_ids=[];
        for (var i = 0; i < root_nodes.length; i++) { root_node_ids.push(root_nodes[i].id); }

        init_open=$.grep(init_open,function( id ) { return $.inArray(id,root_node_ids)!=-1 });
        left_to_open=init_open.length;
        if ($("#" + tree.name + " > ul > li:first").attr("id") == 'noDatasetsFound' || left_to_open == 0) {
            if (typeof tree.onload !== 'undefined') {
                eval(tree.onload);
            }
        }
    });

    $("#" + tree.name).bind("select_node.jstree", function(event, data) {
        trees[tree.name].selected = $('#' + tree.name).jstree('get_selected').attr('id');
        eval($('#' + tree.name).jstree('get_selected').attr('action'));
    });

    $("#" + tree.name).bind("deselect_node.jstree", function(event, data) {
        trees[tree.name].selected = null;
        eval(trees[tree.name].deselect_action);
    });
}

function loadOpenChildren(tree,node) {
    children = tree._get_children(node);
    for (var i = 0; i < children.length; i++) {
        var childId = '#' + children[i].id;
        if (tree.is_open(childId)) {
            tree.close_node(childId);
            childrenLoading++;
            tree.open_node(childId);
        }
    }
    
}
