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

    var css = document.createElement("link")
    css.setAttribute("rel", "stylesheet")
    css.setAttribute("type", "text/css")
    css.setAttribute("href", "/tree/includes/css/jstree.css")
    document.getElementsByTagName("head")[0].appendChild(css);

    document.write('<div class="treecontainer">');
    document.write('    <div class="treetype-wrapper">');
    document.write('        <span class="treetype">');

    if (typeof tree === 'undefined' || typeof tree.start === 'undefined') {
        document.write('            <strong>List by:</strong>');
        document.write('            <select id="treetype-selector" onchange="trees[\'' + tree.name + '\'].type=this.value;updateTree(trees[\'' + tree.name + '\']);">');
        document.write('                <option value="ra"');
        if (tree.type == "ra") document.write(' selected');
        document.write('>Research Award</option>');
        document.write('                <option value="re"');
        if (tree.type == "re") document.write(' selected');
        document.write('>Researcher</option>');
        document.write('                <option value="in"');
        if (tree.type == "in") document.write(' selected');
        document.write('>Institution</option>');
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
    $("#" + tree.name).jstree({
        "core": {
            "html_titles": true,
            "initially_open": tree.init_open,
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
//                    alert(url);
                    return url;
                },
                "success": function (new_data) {
                    return new_data;
                }
            }
        },
        "plugins": [ "json_data", "types", "themes" ]
    });

    $("#" + tree.name).bind("after_open.jstree", function(event, data) {
        childrenLoading--;
        loadOpenChildren(data.inst,data.rslt.obj);
        var settings = data.inst._get_settings();
        if (childrenLoading < 1) {
            settings.core.animation = tree.animation;
            if (typeof tree.onload !== 'undefined') {
                eval(tree.onload);
            }
        }
    });

    $("#" + tree.name).bind("loaded.jstree", function(event, data) {
        loadOpenChildren(data.inst,-1);
    });

    $.vakata.css.add_sheet({ str : '.jstree a { height: auto; }', title : "jstree_override" });
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
