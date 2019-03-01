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
        tree.label = "List by:";
    }
    if (typeof tree.type === 'undefined') {
        tree.type = "ra";
    }
    if (typeof tree.init_open === 'undefined') {
        tree.init_open = "";
    }
    if (typeof tree.animation === 'undefined') {
        tree.animation = 250;
    }
    if (typeof tree.theme === 'undefined') {
        tree.theme = "pelagos";
    }
    if (typeof tree.dots === 'undefined') {
        tree.dots = true;
    }
    if (typeof tree.icons === 'undefined') {
        tree.icons = false;
    }
    trees[tree.name] = tree;

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
        //$.getScript("/includes/jstree/jquery.jstree.js", function (data, textStatus, jqxhr) {
            updateTree(tree);
        //});
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
            "animation": 0,
            "check_callback" : true,
            "data" : {
              "url" : function (node) {
                    var nodeId = "";
                    var url = "";
                    console.log(node);
                    if (node.children.length == 0) {
                        if (tree.type == 'ra') {
                            url = Routing.generate("pelagos_api_tree_get_funding_organizations");
                        } else {
                            url = Routing.generate("pelagos_api_tree_get_letters");
                        }
                    }
                    else {
                        console.log('else');
                        nodeId = node.id;
                        if (tree.type == 'ra') {
                            var matchFundingCycleId = nodeId.match(/^projects_funding-cycle_(\d+)$/);
                            if (null !== matchFundingCycleId) {
                                url = Routing.generate("pelagos_api_tree_get_research_groups_by_funding_cycle", {"fundingCycle": matchFundingCycleId[1]});
                            }
                        } else {
                            var matchLetter = nodeId.match(/^(\D)$/);
                            if (null !== matchLetter) {
                                url = Routing.generate("pelagos_api_tree_get_people", {"letter": matchLetter[1]});
                            } else {
                                var matchPeopleId = nodeId.match(/^projects_peopleId_(\d+)$/);
                                if (null !== matchPeopleId) {
                                    url = Routing.generate("pelagos_api_tree_get_research_groups_by_person", {"personId": matchPeopleId[1]})
                                }
                            }
                        }
                    }
                    return url + "?tree=" + encodeURIComponent(JSON.stringify(tree));
                },
              "data" : function (data) {
                  console.log(data);
                return data;
              }
            },
            "themes":{
                "icons" : tree.icons,
                "dots" : tree.dots,
            },
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
                        if (tree.type == 'ra') {
                            url = Routing.generate("pelagos_api_tree_get_funding_organizations");
                        } else {
                            url = Routing.generate("pelagos_api_tree_get_letters");
                        }
                    }
                    else {
                        nodeId = node.attr('id');
                        if (tree.type == 'ra') {
                            var matchFundingCycleId = nodeId.match(/^projects_funding-cycle_(\d+)$/);
                            if (null !== matchFundingCycleId) {
                                url = Routing.generate("pelagos_api_tree_get_research_groups_by_funding_cycle", {"fundingCycle": matchFundingCycleId[1]});
                            }
                        } else {
                            var matchLetter = nodeId.match(/^(\D)$/);
                            if (null !== matchLetter) {
                                url = Routing.generate("pelagos_api_tree_get_people", {"letter": matchLetter[1]});
                            } else {
                                var matchPeopleId = nodeId.match(/^projects_peopleId_(\d+)$/);
                                if (null !== matchPeopleId) {
                                    url = Routing.generate("pelagos_api_tree_get_research_groups_by_person", {"personId": matchPeopleId[1]})
                                }
                            }
                        }
                    }
                    return url + "?tree=" + encodeURIComponent(JSON.stringify(tree));
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
        console.log('after open');
        childrenLoading--;
        loadOpenChildren(data.instance,data.node);
        var settings = data.instance.settings;
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
        console.log('loaded');
        if (typeof tree.onload !== 'undefined') {
            eval(tree.onload);
        }
        loadOpenChildren(data.instance,-1);
        var root_nodes=data.instance.get_children_dom(-1);
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
        trees[tree.name].selected = data.node.id;
        eval(data.node.a_attr.action);
    });

    $("#" + tree.name).bind("deselect_node.jstree", function(event, data) {
        trees[tree.name].selected = null;
        eval(trees[tree.name].deselect_action);
    });
}

function loadOpenChildren(tree,node) {
    if (tree !== 'undefined') {
        children = tree.get_children_dom(node);
        for (var i = 0; i < children.length; i++) {
            var childId = '#' + children[i].id;
            if (tree.is_open(childId)) {
                tree.close_node(childId);
                childrenLoading++;
                tree.open_node(childId);
            }
        }
    }
}
