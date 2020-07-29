var $ = jQuery.noConflict();

$(document).ready(function() {
    addTree();

    $("#menu .overview").width($("#menu .viewport").width() - 15);

    // Due to a bug in hashChange (old version, no newer compatible with current jQuery),
    // manualy detect the hash on load, and force hash change.
    // But we have to wait with opening nodes until js Tree is fully loaded.
    let tree = $("#tree");
    tree.on("loaded.jstree", function(e, data) {
        if (window.location.hash != "") {
            $(window).trigger("hashchange");
        }
    });

    tree.on('select_node.jstree', function (e, data) {
        data.instance.toggle_node(data.node);
    });

    $(window).on("hashchange", function(){
        var m = location.hash.match(/^#([^\/]+)\/?([^\/]+)?/);
        if (m) {
            if (typeof m[1] !== "undefined") {
                if (typeof m[2] === "undefined") {
                    if ($("#projects_funding-cycle_" + m[1]).length && $("#tree").jstree("get_selected")[0] != "projects_funding-cycle_" + m[1]) {
                        $("#tree").jstree("deselect_all");
                        $("#tree").jstree("select_node", ("#projects_funding-cycle_" + m[1]));
                    }
                }
                else {
                    $("#tree").jstree("open_node", $("#projects_funding-cycle_" + m[1]));
                    if ($("#datasets_projectId_" + m[2]).length && $("#tree").jstree("get_selected")[0] != "datasets_projectId_" + m[2]) {
                        $("#tree").jstree("deselect_all");
                        $("#tree").jstree("select_node", $("#datasets_projectId_" + m[2]), true);
                    }
                    else if ($("#tasks_projectId_" + m[2]).length && $("#tree").jstree("get_selected")[0] != "tasks_projectId_" + m[2]) {
                        $("#tree").jstree("deselect_all");
                        $("#tree").jstree("select_node", $("#tasks_projectId_" + m[2]), true);
                    }
                }
            }
        }
    });
});

function showProjects(by,id) {
    $("#content .overview").html("");
    $("#right .spinner").show();
    switch(by) {
        case "fundSrc":
            var url = Routing.generate("pelagos_app_ui_datasetmonitoring_allresearchgroup", {"id": id, "renderer": "browser"});
            break;
        case "projectId":
            var url = Routing.generate("pelagos_app_ui_datasetmonitoring_researchgroup", {"id": id, "renderer": "browser"});
            break;
        case "peopleId":
            var url = Routing.generate("pelagos_app_ui_datasetmonitoring_researcher", {"id": id, "renderer": "browser"});
            break;
    }
    $.ajax({
        "url": url,
        "success": function(data) {
            $("#content .overview").html(data);
            $("#content .overview td[title]").qtip({
                position: {
                    my: "right bottom",
                    at: "center",
                    adjust: {
                        x: -8
                    },
                    viewport: $(window)
                },
                show: {
                    event: "mouseenter focus",
                    solo: true
                },
                hide: {
                    fixed: true,
                    delay: 100
                },
                style: {
                    classes: "qtip-default qtip-tipped"
                }
            });
            $("#content .overview th[title]").qtip({
                position: {
                    my: "bottom center",
                    at: "top center",
                    viewport: $(window)
                },
                show: {
                    event: "mouseenter focus",
                    solo: true
                },
                hide: {
                    fixed: true,
                    delay: 100
                },
                style: {
                    classes: "qtip-default qtip-tipped"
                }
            });
            $("#content .overview td.details").each(function() {
                var udi = $(this).parent().attr("udi");
                $(this).qtip({
                    content: {
                        text: "loading...",
                        ajax: {
                            url: Routing.generate("pelagos_app_ui_datasetmonitoring_datasetdetails", {"udi": udi}),
                            loading: false
                        }
                    },
                    position: {
                        my: "right bottom",
                        at: "middle left",
                        viewport: $(window),
                        effect: false,
                        target: $(this).parent()
                    },
                    show: {
                        event: "mouseenter focus",
                        solo: true
                    },
                    hide: {
                        fixed: true,
                        delay: 100
                    },
                    style: {
                        classes: "qtip-default qtip-tipped"
                    }
                });
            });

            $("#content .overview table.tablesorter").tablesorter({
                sortList: [[0,0]],
                sortRestart : true,
                sortInitialOrder: "asc"
            });

            graphDatasetStatus();
        }
    })
    .always(function() {
        $("#right .spinner").hide();
        addPdfUrl();
        addCsvUrl()
    });
}

const addPdfUrl = () => {
    const pdfElementIds = document.getElementsByClassName('awesome-icon-pdf');

    for (let i = 0; i < pdfElementIds.length ; i++) {
        const url = pdfElementIds[i].dataset.url;
        pdfElementIds[i].addEventListener("click", function (event) {
                window.open(url);
            },
            false);
    }
}

const addCsvUrl = () => {
    const csvElementIds = document.getElementsByClassName('awesome-icon-csv');
    for (let i = 0; i < csvElementIds.length ; i++) {
        csvElementIds[i].addEventListener("click", function (event) {
                axios.create({})
                    .post(Routing.generate("pelagos_app_ui_reportresearchgroupdatasetstatus_post"), {ResearchGroupSelector: csvElementIds[i].dataset.researchGroupId})
                    .then(response => {
                        const headerval = response.headers['content-disposition'];
                        var filename = headerval.split(';')[1].split('=')[1].replace('"', '').replace('"', '');
                        fileDownload(response.data, filename);
                    })
                    .catch(function (error) {
                        showDialog(`Error: There was an error processing your request.
                            Please contact GRIIDC (<a href=mailto:griidc@gomri.org>griidc@gomri.org</a>)`);
                    });
            },
            false);
    }
}

function updateHash(fund_src,project_id) {
    if (typeof(project_id) != "undefined") {
        location.href = "#" + fund_src + "/" + project_id;
    } else {
        location.href = "#" + fund_src;
    }
}

function addTree() {
    insertTree({
        theme: "pelagos",
        max_depth: 2,
        expand_to_depth: 0,
        animation: 250,
        type: "ra",

        rfp_color: "#00A",
        rfp_action: "updateHash(\{\{fundSrc.id\}\});showProjects('fundSrc',\{\{fundSrc.id\}\});",

        project_color: "#00A",
        project_action: "updateHash(\{\{project.fundingCycle.id\}\},\{\{project.id\}\});showProjects('projectId',\{\{project.id\}\});",

        researcher_color: "#00A",
        researcher_action: "location.href='#';showProjects('peopleId',\{\{person.id\}\});",

        afteropen: "$(window).trigger('hashchange');"
    });
}
