//dataArray stores data for each tab
var datasetList = new Array(4);
var buffer = []; //temporary buffer array to get new bulk of data

var $ = jQuery.noConflict();

var myGeoViz = new GeoViz();

$(document).ready(function() {
    if (typeof($.cookie) == "function" && $.cookie("expanded") == 1) {
        expand();
    }

    myGeoViz.initMap("olmap",{"onlyOneFeature":false,"allowModify":false,"allowDelete":true,"labelAttr":"udi"});

    $(document).on("overFeature",function(e,eventVariables) {
        $('table.datasets tr[udi="' + eventVariables.attributes.udi + '"] td').addClass("highlight");
    });
    $(document).on("outFeature",function(e,eventVariables) {
        $('table.datasets tr[udi="' + eventVariables.attributes.udi + '"] td').removeClass("highlight");
    });

    $("#filter-input").bind("keypress", function(e) {
        if(e.keyCode==13){
            applyFilter();
        }
    });

    $("#expand-collapse").click(function(){
        if ($("#expand-collapse").hasClass("collapsed")) {
            expand();
        }
        else {
            collapse();
        }
    });

    expand();

    $("#map_pane").mouseleave(function() {
        myGeoViz.unhighlightAll();
    });

    $(document).on("filterDrawn",function() {
        $("body").css("cursor","");
        $("#olmap").css("cursor","");
        $("input").css("cursor","");
        trees["tree"].geo_filter=myGeoViz.getFilter();
        applyFilter();
        $("#clearGeoFilterButton").button("disable");
    });

    $("#show_extents_checkbox").button();
    $(".map_button").button();
    // local variable for filter button//
    var filterButton = $("#filter-button");
    filterButton.button();
    filterButton.button("disable");
    $("#clear-button").button();
    $("#filter-input").on("keyup change paste input propertychange", function(e) {
        enableFilterButton();
    });
});
//end document ready

// function to enable the Filter button only when the textbox is not empty //
function enableFilterButton() {
    if ("" !== $("#filter-input").val()) {
        $("#filter-button").button("enable");
    } else {
        $("#filter-button").button("disable");
    }
}

function expand() {
    $("#left").show();
    $("#right").animate({"left" : "45%", "width" : "55%"}, {duration: "slow"});
    $("#left").animate({"width" : "45%"}, {duration: "slow", complete: function() {
        $("#expand-collapse").removeClass("collapsed");
        $(".right-panel").removeClass("right-panel-collapsed");
    }});
    $("#expand-collapse > div").css("background-image", "url(" + $("#expand-collapse").attr("collapse-image") + ")");
    if (typeof($.cookie) == "function") $.cookie("expanded", 1);
}

function collapse() {
    $("#right").animate({"left" : "0%", "width" : "100%"}, {duration: "slow"});
    $("#left").animate({"width" : "0%"}, {duration: "slow", complete: function() {
        $("#expand-collapse").addClass("collapsed");
        $("#left").hide();
        $(".right-panel").addClass("right-panel-collapsed");
    }});
    $("#expand-collapse > div").css("background-image", "url(" + $("#expand-collapse").attr("expand-image") + ")");
    if (typeof($.cookie) == "function") $.cookie("expanded", 0);
}

function resizeLeftRight() {
    $("#left").height(0);
    $("#right").height(0);
    rh = $("#main").height() - $("#filter").height() - $(".tabs").height() - 15;
    lh = $("#main").height() - $(".tabs").height() - 15;
    $("#left").height(lh);
    $("#right").height(rh);
}

function formatBytes(bytes,decimals) {
    if(bytes == 0) return "0 Bytes";
    var k = 1024,
        dm = decimals <= 0 ? 0 : decimals || 2,
        sizes = ["Bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"],
        i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + " " + sizes[i];
}

function getActiveTabIndex() {
    var activeTabIndex = parseInt($("#tabs").tabs("option","active"));
    if (isNaN(activeTabIndex)) {
        activeTabIndex = 0;
    }
    return activeTabIndex;
}

//this function gets data from the server-side and parse it to a buffer array
function loadData(by, id) {
    var activeTabIndex = getActiveTabIndex();
    geo_filter = "";
    if (!by && !id) {
        by = $("#by-input").val()
        id = $("#id-input").val()
    }

    if (trees["tree"].geo_filter) {
        geo_filter = trees["tree"].geo_filter;
    }
    //this gets the data from the search in a bulk based on bulkSize and currentIndex (similar to index for pagination)
    return jqXHR = $.ajax({
        "url": Routing.generate("pelagos_app_ui_datadiscovery_search", { "id": id }),
        "data": {
            "filter": jQuery("#filter-applied").val(),
            "by": by,
            "id": id,
            "geo_filter": geo_filter,
            "active_tab_index": activeTabIndex,
            "current_index": datasetList[activeTabIndex].length > 0 ? datasetList[activeTabIndex].length : 0,
            "bulk_size": 30 //determine the number of row get back from every new load
        },
        "dataType": "json",
        "success": function(response) {
            buffer = JSON.parse(response);

        },
        "error": function(jqXHR, textStatus, errorThrown) {
            console.log("Fail: " + textStatus + " " + errorThrown + jqXHR.getResponseHeader());
        }

    }).done(function(){ //addRows to the table
       addRows();
    });
};

//this iterates new bulk of data and render new rows
function addRows() {
    var activeTabIndex = getActiveTabIndex();

    for (var i = 0; i < buffer.length; i++) {
        var dataset = buffer[i];
        var data = dataset["_source"];
        var row = document.createElement("tr");
        $(row).attr("udi", data["udi"]);
        $(row).attr("datasetid", dataset["_id"]);
        if (data["geometry"]) {
            $(row).hover(function () {
                //show geometries on the map on hovering on the row
                if (!$("#show_extents_checkbox").is(":checked")) {
                    for (var i = 0; i < datasetList[activeTabIndex].length; i++) {
                        if (datasetList[activeTabIndex][i]["_source"]["udi"] == $(this).attr("udi")) {
                            myGeoViz.addFeatureFromWKT(datasetList[activeTabIndex][i]["_source"]["geometry"], {"udi": datasetList[activeTabIndex][i]["_source"]["udi"]});
                            break;
                        }
                    }
                }
                myGeoViz.highlightFeature("udi", $(this).attr("udi"));
            }, function () {
                myGeoViz.unhighlightFeature("udi", $(this).attr("udi"));
                if (!$("#show_extents_checkbox").is(":checked")) {
                    myGeoViz.removeAllFeaturesFromMap();
                }
            });
            //add newly rendered geometry if ShowAllExtents is enabled
            if ($("#show_extents_checkbox").is(":checked")) {
                myGeoViz.addFeatureFromWKT(data["geometry"], {"udi": data["udi"]});
            }
        }

        var rowContent = createRow(data, row);
        $(row).html(rowContent);
        //append row to the table
        $("table.datasets[tabIndex=" + activeTabIndex + "]").append(row);
        //add the data to the data array
        datasetList[activeTabIndex].push(dataset);
    };
    //clear buffer
    buffer = [];
}

//This renders row based on the given data and the currently active tab
function createRow(data, row)
{
    var activeTabIndex = getActiveTabIndex();
    //clone from template row and shove actual data in
    var rowContent = $("#template-data-row-available").children().clone();

    var imgTitle = "";
    switch (activeTabIndex) {
        case 0: //available
            if ("RemotelyHosted" === data["datasetSubmission"]["datasetFileTransferStatus"] && !data["datasetSubmission"]["datasetFileTransferStatus"].match("!^https?://data.gulfresearchinitiative.org!")) {
                imgTitle = "This dataset can be downloaded from an external repository.";
            } else {
                imgTitle = ("title", "Download dataset");
            }
            $(rowContent).find("#img-download-package").removeClass("greyout");
            $(rowContent).find("#dataset-download").click( function(){
                var id = $(this).closest("tr").attr("datasetid");
                startDownload(id);
            });

            if (!data["datasetSubmission"]["datasetFileSize"]) {
                $(rowContent).find("#container-dataset-filesize").hide();
            }
            break;
        case 1: //restricted
            if ("RemotelyHosted" === data["datasetSubmission"]["datasetFileTransferStatus"]) {
                imgTitle = "This dataset is restricted for download but is hosted by another website so availability status is not guaranteed to be accurate. " +
                    "Please contact the external repository for information on how to access this dataset.";
            } else {
                imgTitle = "This dataset is restricted for download.";
            }
            $(rowContent).find("#dataset-restrictions").text("Download Restricted");
            $(rowContent).find("#container-dataset-restrictions").show();

            if (!data["datasetSubmission"]["datasetFileSize"]) {
                $(rowContent).find("#container-dataset-filesize").hide();
            }
            break;
        case 2: //InReview
                imgTitle = "Download unavailable"

                if (!data["datasetSubmission"]["datasetFileSize"]) {
                    $(rowContent).find("#container-dataset-filesize").hide();
                }
            break;
        case 3: //identified
            $(rowContent).find("#img-download-package").hide();
            $(rowContent).find("#container-dataset-restrictions").css("color","grey");
            $(rowContent).find("#dataset-restrictions").text("?");
            $(rowContent).find("#container-dataset-restrictions").show();
            $(rowContent).find("#container-dataset-filesize").css("color","grey");
            $(rowContent).find("#dataset-filesize").text("?");
            break;
        default:
            break;
    }
    $(rowContent).find("#img-download-package").attr("title", imgTitle);


    if (!data["datasetSubmission"]["authors"]) {
        $(rowContent).find("#container-dataset-authors").hide();
    } else {
        $(rowContent).find("#dataset-authors").text(data["datasetSubmission"]["authors"]);
    }

    if (!data["year"]) {
        $(rowContent).find("#container-dataset-year").hide();
    } else {
        $(rowContent).find("#dataset-year").text(data["year"]);
    }

    $(rowContent).find("#dataset-title").attr("href", $(rowContent).find("#dataset-title").attr("href").replace("placeholder-udi", data["udi"]) );
    $(rowContent).find("#dataset-title").text(data["title"]);

    if (!data["researchGroup"]["name"]) {
        $(rowContent).find("#container-dataset-research-group").hide();
    } else {
        $(rowContent).find("#dataset-research-group").text(data["researchGroup"]["name"]);
    }

    if (!data["doi"]["doi"]) {
        $(rowContent).find("#container-dataset-doi").hide();
    } else {
        $(rowContent).find("#dataset-doi").attr("href", $(rowContent).find("#dataset-doi").attr("href").replace("placeholder-doi", data["doi"]["doi"]));
        $(rowContent).find("#dataset-doi").text(data["doi"]["doi"]);
    }

    $(rowContent).find("#dataset-udi").attr("href", $(rowContent).find("#dataset-udi").attr("href").replace("placeholder-udi", data["udi"]));
    $(rowContent).find("#dataset-udi").text(data["udi"]);

    var filesize = parseInt(data["datasetSubmission"]["datasetFileSize"]);
    if (filesize) {
        if (filesize > 1073741824) {
            $(rowContent).find("#dataset-filesize").css("color","red");
        }
        $(rowContent).find("#dataset-filesize").text(formatBytes(filesize,0));
    }

    //this shows the details dataset on Show Details button
    $(".details_link").bind("click", function(){
        var row = $(this).closest("tr");
        if ($(row).has(".details:empty").length == 1) {
            var datasetId = $(row).attr("datasetid");
            $.ajax({
                "url": Routing.generate("pelagos_app_ui_datadiscovery_showdetails", { "id": datasetId }),
                "success": function(data) {
                    $(row).find(".details").html(data);
                    $(row).find(".details").show();
                    $(row).find(".details_link").html("Hide Details");
                }
            });
        } else {
            if ($(row).find(".details:visible").length == 1) {
                $(row).find(".details").hide();
                $(row).find(".details_link").html("Show Details");
            } else {
                $(row).find(".details").show();
                $(row).find(".details_link").html("Hide Details");
            }
        }
    });

    return rowContent;
}
//end createRow

//this occurs at initial/new search
function showDatasets(by,id) {
    var filter = jQuery("#filter-applied").val();
    geo_filter = "";
    if (trees["tree"].geo_filter) {
        geo_filter = trees["tree"].geo_filter;
    }

    //reset existing data to prepare for a new search
    datasetList[0] = [];
    datasetList[1] = [];
    datasetList[2] = [];
    datasetList[3] = [];
    myGeoViz.removeAllFeaturesFromMap();

    //enable this
    $("#show_extents_checkbox").button();
    $("#filter-button").button("disable");
    $("#clear-button").button("disable");

    $("#drawGeoFilterButton").button("disable");
    currentlink = $("#packageLink").attr("href");
    if (currentlink) {
        newlink = currentlink.replace(/\?filter=[^&]*(&|$)/,"");
        if ($("#filter-applied").val() != "") {
            newlink += "?filter=" + $("#filter-applied").val();
        }
        $("#packageLink").attr("href",newlink);
    }

    //get number of results for each tabs
    $.ajax({
        "url": Routing.generate("pelagos_app_ui_datadiscovery_count"),
        "data": {
            "filter": filter,
            "by": by,
            "id": id,
            "geo_filter": geo_filter
        },
        "success": function(data) {
            $("#dataset_listing_wrapper .spinner").hide();
            loadData(by, id);
            $("#dataset_listing").html(data);
            $("#tabs").tabs({
                activate: function(event, ui) {
                    var activeTabIndex = getActiveTabIndex();
                    if (datasetList[activeTabIndex].length == 0) {
                        loadData(by, id);
                    }

                    if ($("#show_extents_checkbox").is(":checked")) {
                        displayActiveTabExtents();
                    }
                }
            });
            enableFilterButton();
            $("#clear-button").button("enable");
            $("#drawGeoFilterButton").button("enable");
            //this triggers infinite scrolling
            $(".viewport").has(".datasets").scroll(function(){
                if ($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight) {
                    loadData(by, id);
                }
            });
            if (geo_filter) {
                $("#clearGeoFilterButton").button("enable");
            }
        },
        "error": function(jqXHR, textStatus, errorThrown) {
            alert("Fail: " + textStatus + " " + errorThrown + jqXHR.getResponseHeader());
        }
    });
}

function applyFilter() {
    $("#filter-button").button("disable");
    $("#clear-button").button("disable");
    $("#drawGeoFilterButton").button("disable");
    myGeoViz.removeAllFeaturesFromMap();
    $("#dataset_listing").html("");
    $("#dataset_listing_wrapper .spinner").show();
    trees["tree"].filter=jQuery("#filter-input").val();
    jQuery("#filter-applied").val(jQuery("#filter-input").val());
    updateTree(trees["tree"]);
}

function clearAll() {
    myGeoViz.goHome();
    $("#by-input").val("");
    $("#id-input").val("");
    $("#filter-input").val("");
    $("#filter-applied").val("");
    trees["tree"].selected = null;
    myGeoViz.clearFilter();
    $("#clearGeoFilterButton").button("disable");
    trees["tree"].geo_filter = null;
    applyFilter();
}

//this function adds geometries from rendered data to the map
function displayActiveTabExtents()
{
    myGeoViz.removeAllFeaturesFromMap();
    var activeTabIndex = getActiveTabIndex();
    if (datasetList[activeTabIndex]) {
        for (var i=0; i<datasetList[activeTabIndex].length; i++) {
            if (datasetList[activeTabIndex][i]["_source"]["geometry"]) {
                myGeoViz.addFeatureFromWKT(datasetList[activeTabIndex][i]["_source"]["geometry"], {"udi": datasetList[activeTabIndex][i]["_source"]["udi"]});
            }
        }
    }
}

function showAllExtents() {
    if ($("#show_extents_checkbox").is(":checked")) {
        $("#show_extents_label").html('<span class="ui-button-text">Hide Extents</span>');
        displayActiveTabExtents();
    } else {
        $("#show_extents_label").html('<span class="ui-button-text">Show Extents</span>');
        $("table.datasets tr td").removeClass("highlight");
        myGeoViz.removeAllFeaturesFromMap();
    }
}

function addTree() {
    insertTree({
        start: "ra",
        title: "Filter by Research Award",
        theme: "pelagos",
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