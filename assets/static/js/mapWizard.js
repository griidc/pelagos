function MapWizard(json)
{
    var json;

    var seed = Math.round(Math.random()*1e10);

    var buttonText = "The Spatial Extent Wizard guides the user in creating the Spatial Extent for a dataset. Users can 1) Create a description to indicate that the data has no spatial or temporal extent, or 2) Define the spatial extent of your dataset by providing a list of coordinates (in decimal degrees) or drawing on a map. GRIIDC prefers that you submit a list of coordinates to ensure the most accurate representation of your data.";

    var featureSend = false;
    var drawTheMap = false;
    var startOffDrawing = true;
    var orderEnum;
    var errMsg;
    var geometryType;

    var wizGeoViz;

    var divSmallMap = "#"+json.divSmallMap;
    var divSpatialWizard ="#"+json.divSpatialWizard;
    var divNonSpatial = "#"+json.divNonSpatial;
    var gmlField = "#"+json.gmlField;
    var descField = "#"+json.descField;
    var validateGeometry = json.validateGeometry;
    var inputGmlControl = json.inputGmlControl;
    var diaWidth = $(window).width()*.8;
    var diaHeight = $(window).height()*.8;

    $.ajaxSetup({
        timeout: 60000,
    });

    init();

    this.flashMap = function()
    {
        smlGeoViz.flashMap();
        var containerWidth = $("#"+json.divSmallMap).closest("tbody").width();

        var smallMapWidth = ((containerWidth / 2) * .95);
        var smallMapHeight = ((smallMapWidth / 4) * 2.5);

        $("#"+json.divSmallMap).height(smallMapHeight);
        $("#"+json.divSmallMap).width(smallMapWidth);
    }

    this.cleanMap = function()
    {
        smlGeoViz.goHome();
        smlGeoViz.removeImage();
        smlGeoViz.removeAllFeaturesFromMap();
        smlGeoViz.flashMap();
    }

    this.haveGML = function(gml)
    {
        smlGeoViz.goHome();
        smlGeoViz.removeImage();
        smlGeoViz.removeAllFeaturesFromMap();
        smlGeoViz.gmlToWKT(gml)
            .then(function (wkt){
                smlGeoViz.removeAllFeaturesFromMap();
                var addedFeature = smlGeoViz.addFeatureFromWKT(wkt);
                smlGeoViz.gotoAllFeatures();
                geometryType = smlGeoViz.getSingleFeatureClass();
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                pelagosUI.loadingSpinner.hideSpinner();
                handleError(jqXHR);
            });
    }

    function handleError(jqXHR)
    {
        let message = "";
        if (jqXHR.status & jqXHR.status !== 0) {
            message = jqXHR.responseText == null ? errorThrown: jqXHR.responseJSON.message;
        }
        pelagosUI.loadingSpinner.hideSpinner();
        pelagosUI.showErrorDialog(message);
    }

    function init()
    {
        smlGeoViz = new GeoViz();
        smlGeoViz.initMap(json.divSmallMap,{"onlyOneFeature":false,"allowModify":false,"allowDelete":false,"staticMap":true});

        $(divSpatialWizard).html('<fieldset><div class="ui-widget-header ui-corner-all"><button style="color:#039203;font-size:larger;width:100%;" id="geowizBtn" type="button">Define Spatial Extent</button></div><p>'+buttonText+"</p></fieldset>").show();

        $(divNonSpatial).hide();
        $("#"+json.descField).hide();

        $(document.body).append('<div id="divMapWizard">');
        $("#divMapWizard").load(Routing.getBaseUrl() + '/geoviz/wizard_dialog.html');

        $(gmlField).change(function() {
            smlGeoViz.goHome();
            smlGeoViz.removeImage();
            smlGeoViz.removeAllFeaturesFromMap();
            smlGeoViz.gmlToWKT($(gmlField).val())
                .then(function (wkt){
                    smlGeoViz.removeAllFeaturesFromMap();
                    var addedFeature = smlGeoViz.addFeatureFromWKT(wkt);
                    smlGeoViz.gotoAllFeatures();
                    geometryType = smlGeoViz.getSingleFeatureClass();
                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    pelagosUI.loadingSpinner.hideSpinner();
                    handleError(jqXHR);
                })
                .always(function () {
                    pelagosUI.loadingSpinner.hideSpinner();
                });
        });

        $("#geowizBtn").button().click(function()
        {
            showSpatialDialog();
        });

        var containerWidth = $("#"+json.divSmallMap).closest("tbody").width();

        var smallMapWidth = ((containerWidth / 2) * .95);
        var smallMapHeight = ((smallMapWidth / 4) * 2.5);

        $("#"+json.divSmallMap).height(smallMapHeight);
        $("#"+json.divSmallMap).width(smallMapWidth);
    }

    function initWiz()
    {
        return $.Deferred(function() {
            pelagosUI.loadingSpinner.showSpinner();

            var wizPromise = this;
            //Synchonous load of HTML, then append to DIV
            $.ajax({
                url: Routing.getBaseUrl() + '/geoviz/wizard_map.html',
                success: function(html) {
                    $("#divMapWizard").append(html);
                }
            }).then( function() {
                wizGeoViz = new GeoViz();

                var mymap = $("#mapwiz table#maptoolstbl tbody tr td").first();
                $(mymap).append("<div />").attr("id","olmap").css({width:100,height:600});
                wizGeoViz.initMap("olmap",{"onlyOneFeature":true,"allowModify":true,"allowDelete":true});

                $("#coordTabs").tabs();

                $.fn.qtip.defaults = $.extend(true, {}, $.fn.qtip.defaults, {
                    show: {
                        event: "mouseenter mouseover"
                    },
                    hide: {
                        event: "mouseleave mouseout click"
                    },
                    position: {
                        adjust: {
                            method: "flip flip"
                        },
                        my: "right bottom",
                        at: "center left",
                        viewport: $(window)
                    },
                    style:
                    {
                        classes: "qtip-tipped qtip-shadow customqtip"

                    }
                });

                $("#coordForm").validate({
                    rules: {
                        maxLat: {
                            required: true,
                            range: [-90, 90],
                            number: true
                        }
                    }
                });

                setEvents();

                //only show input GML tab on dataset-review
                if (true === inputGmlControl) {
                    $('#coordTabs a[href="#gmlTab"]').parent().show();
                } else {
                    $('#coordTabs a[href="#gmlTab"]').parent().hide();
                }

                wizPromise.resolve();
            })
            .fail(function (jqXHR, errorThrown, errorText) {
                pelagosUI.loadingSpinner.hideSpinner();
                handleError(jqXHR);
            })
            .always(function () {
                pelagosUI.loadingSpinner.hideSpinner();
            });
        });
    }

    function showWizard()
    {
        $.when(initWiz())
        .then(function(){
            orderEnum = wizGeoViz.orderEnum;

            $("#helpinfo").dialog({
                autoOpen: false,
                width: 400,
                modal: true,
                buttons: {
                    OK: function() {
                        if ($("#drawPolygon:checked").length){$("#drawPolygon:checked").click();$("#coordTabs").tabs({ active: 0 });}
                        if ($("#drawLine:checked").length){$("#drawLine:checked").click();$("#coordTabs").tabs({ active: 0 });}
                        if ($("#drawPoint:checked").length){$("#drawPoint:checked").click();$("#coordTabs").tabs({ active: 0 });}
                        if ($("#drawBox:checked").length){$("#drawBox:checked").click();$("#coordTabs").tabs({ active: 1 });}
                        if ($("#featDraw:checked").length){$("#featDraw:checked").click();}
                        if ($("#featPaste:checked").length){$("#featPaste:checked").click();}

                        if (startOffDrawing)
                        {wizGeoViz.startDrawing();}
                        else
                        {
                            $("#coordlist").focus();
                        }

                        if (!$("#drawPolygon:checked").length && !$("#drawLine:checked").length && !$("#drawPoint:checked").length && !$("#drawBox:checked").length && !$("#featDraw:checked").length && !$("#featPaste:checked").length)
                        {alert("Please make a selection!");}
                        else
                        {
                            $(this).dialog("close");
                            wizGeoViz.updateMap();
                        }
                    }
                }
            });

            $(document).on("imready", function(e,who) {
                if (who == "#olmap")
                {
                    if (drawTheMap)
                    {
                        drawMap();
                    }
                }
            });

            switch (geometryType)
            {
                case "Polygon":
                    $("#drawPolygon").click();
                    break;
                case "Point":
                    $("#drawPoint").click();
                    break;
                case "MultiPoint":
                    $("#drawPoint").click();
                    break;
                case "Line":
                    $("#drawLine").click();
                    break;
            }

            drawMap();
        });
    }

    function showSpatialDialog()
    {
        $("#hasSpatial").dialog({
            width: 700,
            height: 250,
            modal: true,
            title: "Spatial Extent Wizard - 1",
            buttons: {
                "Spatial": function() {
                   $(this).dialog("close");
                   showWizard();
                },
                "Non-Spatial": function() {
                    $(this).dialog("close");
                    noSpatial();
                },
            }
        });
    }

    function noSpatial()
    {
        hasSpatial(true);
        $("#provideDesc").dialog({
            width: 700,
            height: 350,
            modal: true,
            title: "Spatial Extent Wizard - 2",
            buttons: {
                "OK": function() {
                    $(this).dialog("close");
                    noSpatialClose();
                }
            }
        });

        $("#wizDesc").focus();
    }

    function noSpatialClose()
    {
        $(descField).val($("#wizDesc").val());
        $(descField).focus();
        $(gmlField).val("");
        $(gmlField).trigger("change");
        $("#wizDesc").val("")
    }

    this.haveSpatial = function(Spatial)
    {
        hasSpatial(Spatial);
    }

    function hasSpatial(Spatial)
    {

        if (json.spatialFunction != "")
        {
            window[json.spatialFunction](Spatial);
        }

        if (Spatial)
        {
            $("#"+json.divNonSpatial).show();
            $("#"+json.divSpatial).hide();
            $("#"+json.descField).show();
        }
        else
        {
            $("#"+json.divSpatial).show();
            $("#"+json.divNonSpatial).hide();
        }
    }

    function drawMap()
    {
        hasSpatial(false);

        $("#mapwiz").dialog({
            height: diaHeight,
            width: diaWidth,
            modal: true,
            title: "Spatial Extent Wizard - 3",
            close: function(event, ui) { closeDialog() },
            resizeStop: function(){
                wizGeoViz.updateMap();
            },
            dragStop: function(){
                wizGeoViz.updateMap();
            }
        });

        finalizeMap();
    }

    function finalizeMap()
    {
        var gml = $(gmlField).val();
        if (gml != "")// && !featureSend)
        {
            wizGeoViz.gmlToWKT(gml).then(function (wkt){
                var addedFeature = wizGeoViz.addFeatureFromWKT(wkt);
                $("#coordlist").val(wizGeoViz.getCoordinateList(addedFeature.id));
                $("#inputGml").val(gml);
            });
            featureSend = true;
        }
        else if (gml == "")
        {
            $("#helpinfo").dialog("open");
        }

        fixMapToolHeight();

        wizGeoViz.updateMap();
    }

    function whatIsCoordinateOrder()
    {
        //todo: remove dialogs from if's, only one end dialog, and test wizAddFeature result for additional dialog.

        var coordList = $("#coordlist").val();
        var whatOrder = wizGeoViz.determineOrder(coordList);
        var diaMessage = "";
        var diaButtons = [ {text:"Yes",click:function(){$(this).dialog("close");}},{text:"No",click:function(){$(this).dialog("close");}} ];
        var realOrder = 0;

        if (whatOrder == orderEnum.EMPTY)
        {
            diaMessage = "You didn't enter any (valid) coordinates!";
            diaButtons = [ {text:"OK",click:function(){$(this).dialog("close");}} ];
        }
        else if (whatOrder == orderEnum.LATLONG)
        {
            diaMessage = "This is Latitude, Longitude order, right?";
            diaButtons = [ {text:"Yes",click:function(){$(this).dialog("close");wizAddFeature(orderEnum.LATLONG);}},{text:"No, it's Longitude,Latitude",click:function(){wizAddFeature(orderEnum.LONGLAT);$(this).dialog("close");}} ];
        }
        else if (whatOrder == orderEnum.LATLONGML)
        {
            diaMessage = "Most likely this is Latitude, Longitude order, is this correct?";
            diaButtons = [ {text:"Yes",click:function(){wizAddFeature(orderEnum.LATLONG);$(this).dialog("close");}},{text:"No, it's Longitude,Latitude",click:function(){wizAddFeature(orderEnum.LONGLAT);$(this).dialog("close");}} ];
        }
        else if (whatOrder == orderEnum.LONGLAT)
        {
            diaMessage = "This is Longitude, Latitude order, right?";
            diaButtons = [ {text:"Yes",click:function(){wizAddFeature(orderEnum.LONGLAT);$(this).dialog("close");}},{text:"No, it's Latitude,Longitude",click:function(){wizAddFeature(orderEnum.LATLONG);$(this).dialog("close");}} ];
        }
        else if (whatOrder == orderEnum.LONGLATML)
        {
            diaMessage = "Most likely this is Longitude, Latitude order, is this correct?";
            diaButtons = [ {text:"Yes",click:function(){wizAddFeature(orderEnum.LONGLAT);$(this).dialog("close");}},{text:"No, it's Latitude,Longitude",click:function(){wizAddFeature(orderEnum.LATLONG);$(this).dialog("close");}} ];
        }
        else if (whatOrder == orderEnum.UNKNOWN)
        {
            diaMessage = "What is the coordinate order?";
            diaButtons = [ {text:"Latitude,Longitude",click:function(){wizAddFeature(orderEnum.LATLONG);$(this).dialog("close");}},{text:"Longitude,Latitude",click:function(){wizAddFeature(orderEnum.LONGLAT);$(this).dialog("close");}} ];
        }
        else if (whatOrder == orderEnum.MIXED)
        {
            diaMessage = "The coordinate order seems to be mixed, Please ensure your coordinates are in a consistent order.";
            diaButtons = [ {text:"OK",click:function(){wizAddFeature(orderEnum.MIXED);$(this).dialog("close");}} ];
        }

        $("<div>"+diaMessage+"</div>").dialog({
            autoOpen: true,
            title: "Coordinate Order?",
            height: 200,
            width: 500,
            buttons: diaButtons,
            modal: true,
            close: function (event, ui) {
                $(this).dialog("destroy").remove();
                $(document).trigger("coordinateOrder",realOrder);
                realOrder = 0;
            }
        });
    }

    function wizAddFeature(llOrder)
    {
        var flipOrder = false;

        if (llOrder == orderEnum.LONGLAT)
        {
            flipOrder = true;
        }
        wizGeoViz.removeAllFeaturesFromMap();

        var wktVal = $("#coordlist").val();

        $("#olmap").on("coordinateError", function(e, eventInfo) {
            errMsg = eventInfo;
        });

        var triedAdd = wizGeoViz.addFeatureFromcoordinateList(wktVal,flipOrder);

        if (!triedAdd)
        {
            message = "Those coordinates don't appear to make a valid feature.";
            if (typeof errMsg != "undefined") { message += "<p>Reason:"+errMsg+"</p>";errMsg=undefined;};
            $("<div>"+message+"</div>").dialog({
                height: "auto",
                width: "auto",
                autoOpen: true,
                title: "WARNING!",
                buttons: {
                    OK: function() {
                        $(this).dialog("close");
                    }},
                modal: true,
                close: function (event, ui) {
                    $(this).dialog("destroy").remove();
                }
            });
            return false;
        }
        else
        {
            wizGeoViz.gotoAllFeatures();
            return true;
        }
    }

    function renderOnMap()
    {
        var activeTabIndex = $("#coordTabs").tabs("option", "active");
        $("#saveFeature").button("disable");
        wizGeoViz.stopDrawing();
        wizGeoViz.removeAllFeaturesFromMap();
        switch(activeTabIndex) {
            //coordinate list tab
            case 0:
                whatIsCoordinateOrder();
                break;
            //bounding box tab
            case 1:
                var maxLat = $("#maxLat").val();
                var minLat = $("#minLat").val();
                var maxLong = $("#maxLong").val();
                var minLong = $("#minLong").val();
                renderBoundingBox(maxLat,minLat,maxLong,minLong);
                break;
            //validate gml tab
            case 2:
                renderInputGml();
                break;
        }

        if (activeTabIndex !== 2)
        {
            $("#inputGml").val("");
        }
    }

    function validateGml(gml)
    {
        return $.ajax({
            url: Routing.generate("pelagos_app_gml_validategml"),
            type: "POST",
            data: {gml: gml},
        });
    }

    function renderInputGml()
    {
        var gml = $("#inputGml").val();
        if (gml.trim() === "") {
            showDialog("Warning", "Input GML is empty!");
            return;
        }

        $("#drawOnMap").button("disable");
        validateGml(gml).then(function(data) {
            if (true === data[0]) {
                wizGeoViz.gmlToWKT(gml).fail(function(xhr){
                    if (xhr.status === 400) {
                        showDialog("Geometry Validation", "Invalid (Wkt Conversion");
                    } else {
                        showDialog("Geometry Validation",  xhr.status + ": " + xhr.statusText + " (Wkt Conversion)");
                    }
                    $("#drawOnMap").button("enable");
                }).then(function(wkt){
                    validateGeometryFromWkt(wkt).then(function (isValid) {
                        if (isValid === "Valid Geometry") {
                            wizGeoViz.addFeatureFromWKT(wkt);
                            showDialog("Validation Success", "Geometry & GML are valid!");
                        } else {
                            showDialog("Geometry Validation", $isValid);
                        }
                        $("#drawOnMap").button("enable");
                    });
                });
            } else {
                $("<div>"+ data[1].join("").replace(/\n/g, "<br/>") +"</div>").dialog({
                    height: "auto",
                    width: "auto",
                    autoOpen: true,
                    title: "GML Validation",
                    buttons: {
                        OK: function() {
                            $(this).dialog("close");
                         }},
                    modal: true,
                    close: function (event, ui) {
                        $(this).dialog("destroy").remove();
                    }
                });
                $("#drawOnMap").button("enable");
            }
        });
    }

    function renderBoundingBox(maxLong,minLong,maxLat,minLat)
    {
        var wkt = wizGeoViz.getWKTFromBounds(minLong,minLat,maxLong,maxLat);

        var triedAdd = wizGeoViz.addFeatureFromcoordinateList(wkt);

        if (!triedAdd)
        {
            message = "Those coordinates don't appear to make a valid feature.";
            if (typeof errMsg != "undefined") { message += "<p>Reason:"+errMsg+"</p>";errMsg=undefined;};
            $("<div>"+message+"</div>").dialog({
                height: "auto",
                width: "auto",
                autoOpen: true,
                title: "WARNING!",
                buttons: {
                    OK: function() {
                        $(this).dialog("close");
                    }},
                modal: true,
                close: function (event, ui) {
                    $(this).dialog("destroy").remove();
                }
            });
            return false;
        }
        else
        {
            wizGeoViz.gotoAllFeatures();
            return true;
        }

    }

    function saveFeature()
    {
        var myWKTid = wizGeoViz.getSingleFeature();
        if (typeof myWKTid !== "undefined") {
            if (wizGeoViz.hasMultiFeatures() === true)
            {
                $(gmlField).val($("#inputGml").val());
                $(gmlField).trigger("change");
                $(descField).val("");
                closeDialog();
            }
            else
            {
                var myWKT = wizGeoViz.getWKT(myWKTid);
                var wgsWKT = wizGeoViz.wktTransformToWGS84(myWKT);

                //run GML validation if the SEW is opened with dataset review,
                if (true === validateGeometry) {
                    validateGeometryFromWkt(wgsWKT).then(function(isValid) {
                        if (isValid === "Valid Geometry") {
                            wizGeoViz.wktToGML(wgsWKT).then(function(gml){
                                $(gmlField).val(gml);
                                $(gmlField).trigger("change");
                                $(descField).val("");
                            })
                            .fail(function (jqXHR, textStatus, errorThrown) {
                                pelagosUI.loadingSpinner.hideSpinner();
                                handleError(jqXHR);
                            });
                            closeDialog();
                            pelagosUI.loadingSpinner.hideSpinner();
                        }
                    })
                } else {
                    wizGeoViz.wktToGML(wgsWKT).then(function(gml){
                        $(gmlField).val(gml);
                        $(gmlField).trigger("change");
                        $(descField).val("");
                        closeDialog();
                        pelagosUI.loadingSpinner.hideSpinner();
                    })
                    .fail(function (jqXHR, textStatus, errorThrown) {
                        pelagosUI.loadingSpinner.hideSpinner();
                        handleError(jqXHR);
                    });
                }
            }
        } else {
            $(gmlField).val("");
            closeDialog();
            $(gmlField).trigger("change");
        }
    }

    function validateGeometryFromWkt(wkt)
    {
        return $.ajax({
            url: Routing.generate("pelagos_app_gml_validategeometryfromwkt"),
            type: "POST",
            data: {wkt: wkt},
            success: function(data, textStatus, jqXHR){
                return jqXHR;
            }
        })
        .fail(function(xhr)
        {
            if(xhr.status === 400) {
                showDialog("Invalid Geometry", xhr.responseText);
            }
            else handleError(xhr);
        });
    }

    function setEvents()
    {
        $("#olmap").on("closeMe", function(e, eventInfo) {
            closeDialog();
        });

        $("#olmap").on("featureAdded", function(e, eventInfo) {
            //populate
            var wkt = wizGeoViz.getWktFromFeatures();
            var wgsWKT = wizGeoViz.wktTransformToWGS84(wkt);
            if (false === wizGeoViz.hasMultiFeatures())
            {
                $("#coordlist").val(eventInfo);
                //populate bounding box fields
                bbArray = wizGeoViz.getBBOX(wizGeoViz.getSingleFeature());
                //minLong,minLat,maxLong,maxLat
                $("#minLong").val(bbArray[0]);
                $("#minLat").val(bbArray[1]);
                $("#maxLong").val(bbArray[2]);
                $("#maxLat").val(bbArray[3]);
            }
            else
            {
                $("#coordlist").val("");
                $("#minLong").val("");
                $("#minLat").val("");
                $("#maxLong").val("");
                $("#maxLat").val("");
            }
            if (eventInfo.trim() !== "" || $("#inputGml").val() !== "")
            { $("#saveFeature").button("enable"); }
            else
            { $("#saveFeature").button("disable"); }

            if (!wizGeoViz.canDraw())
            { $("#startDrawing").button("enable"); }
            else
            { $("#startDrawing").button("disable"); }

        });

        $("#olmap").on("modeChange", function(e, eventInfo) {
            $("#wizDrawMode").html(eventInfo);
            switch (eventInfo.trim())
            {
                case "Navigation":
                    showNavMode();
                    break;
                case "Drawing":
                    showDrawingMode();
                    break;
                case "Modify":
                    showModifyMode();
                    break;
                default:
                    showNavMode();
                    break;
            }
        });

        $("#olmap").on("vectorChanged", function(e, eventInfo) {
            $("#coordlist").val(eventInfo);
        });

        $("#saveFeature")
        .button({ icons: { primary: "ui-icon ui-icon-disk"}},{disabled: true})
        .click(function()
        {
            pelagosUI.loadingSpinner.showSpinner();
            saveFeature();
        })
        .end()
        .attr("title","Saves extent to the metadata editor and closes wizard")
        .qtip({
            content: $("#saveFeature").attr("title")
        });


        $("#drawOnMap").button({ icons: { primary: "ui-icon ui-icon-check"}})
        .qtip({    content: {
                text: "Re-renders to the exent on the map after changes to the coordinate list"
            }})
        .click(function()
        {renderOnMap();})
            ;

        $("#startDrawing").button({ icons: { primary: "ui-icon ui-icon-pencil"}}).click(function()
        {
            wizGeoViz.startDrawing();
            wizGeoViz.updateMap();
        })
        .qtip({    content: {
            text: "Puts map in drawing mode, only one feature can be drawn on the map at a time"
        }});

        $("#deleteFeature").button({ icons: { primary: "ui-icon ui-icon-trash"}},{disabled: true}).click(function()
        {
            wizGeoViz.deleteSelected();
        })
        .parent()
        .attr("title","Deletes selected feature")
        .qtip({
            content: $("#deleteFeature").attr("title")
        });

        $("#exitDialog").button({ icons: { primary: "ui-icon ui-icon-refresh"}}).click(function()
        {
            wizGeoViz.removeAllFeaturesFromMap();
            smlGeoViz.removeAllFeaturesFromMap();
            $("#coordlist").val("");
            $("#inputGml").val("");
            closeDialog();
            showSpatialDialog();
        })
        .qtip({    content: {
            text: "Restart wizard from beginning"
        }});

        $("#startOver").button({ icons: { primary: "ui-icon ui-icon-wrench"}}).click(function()
        {
            wizGeoViz.stopDrawing();
            wizGeoViz.removeAllFeaturesFromMap();
            $("#coordlist").val("");
            $("#inputGml").val("");
            $("#saveFeature").button("disable");
            $("#startDrawing").button("enable");
            wizGeoViz.goHome();
            $("#helpinfo").dialog("open");
        })
        .qtip({    content: {
            text: "Reselect geometry type and mode"
        }});

        $("#polygonMode").button().click(function()
        {wizGeoViz.setDrawMode("polygon");});

        $("#boxMode").button().click(function()
        {wizGeoViz.setDrawMode("box");});

        $("#drawPolygon").button().click(function()
        {wizGeoViz.setDrawMode("polygon");});

        $("#drawLine").button().click(function()
        {wizGeoViz.setDrawMode("line");});

        $("#drawPoint").button().click(function()
        {wizGeoViz.setDrawMode("point");});

        $("#drawBox").button().click(function()
        {wizGeoViz.setDrawMode("box");});

        $("#featDraw").button().click(function()
        {startOffDrawing=true;});

        $("#featPaste").button().click(function()
        {startOffDrawing=false;});

        $("#coordlist").focus(function () {
            showTextMode();
        });

        $("#coordlist").click(function()
        {
            wizGeoViz.stopDrawing();
            showTextMode();
        });

        $("#coordlist").keydown(function()
        {
            wizGeoViz.stopDrawing();
            showTextMode();
        });
    }

    function showTextMode()
    {
        $("#wizDrawMode").html("Text");
        var mapHelpText = "Coordinates should be latitude, longitude, in decimal degrees, but the wizard can accept your coordinate in alternate order. Coordinates in the list can be modified or deleted, click Render on Map to update feature on map.";
        $("#maphelptxt").html(mapHelpText);
    }

    function showNavMode ()
    {
        var mapHelpText = "Drag map to pan, mousewheel or double click to zoom at mouse pointer. Hold Shift, left-click mouse, and drag to draw a box, map will zoom to box.";
        $("#maphelptxt").html(mapHelpText);
        $("#deleteFeature").button("disable");
    }

    function showDrawingMode ()
    {
        var mapHelpText = "Click to add points, double-click to finish drawing. Click feature to select and modify. Select feature and click Delete button to delete feature. Feature points can be modified or deleted in the Coordinate List text box.";
        $("#maphelptxt").html(mapHelpText);
        $("#deleteFeature").button("disable");
    }

    function showModifyMode ()
    {
        var mapHelpText = "Drag hollow circles to move vertexes, drag solid midpoint circles to create new vertexes. Click hollow circle and press 'Delete' on keyboard to delete vertex, click Delete button to delete entire feature. Click map outside of feature to end modify mode.";
        $("#maphelptxt").html(mapHelpText);
        $("#deleteFeature").button("enable");
    }

    function verifyMap()
    {
        $(gmlField).val($("#wizCoordChk").val());
        drawMap();
    }

    function closeDialog()
    {
        try {
            $("#mapwiz").dialog("destroy").remove();
        } catch(err) {
            console.log(err.message);
        }
    }

    function validateCoords(Unchecked, Checked)
    {
        $("#"+Checked).text(wizGeoViz.checkPointList($("#"+Unchecked).val()));
        var retval = wizGeoViz.determineOrder($("#"+Checked).val());
    }

    function fixMapToolHeight()
    {
        var tblHgt = $("#maptoolstbl").height();
        tblHgt = tblHgt - $("#wiztoolbar").height();
        tblHgt = tblHgt - $("#coordlistLbl").height();
        tblHgt = tblHgt - 50; //padding

        var coordList = $("#coordlist");
        coordList.height((tblHgt*.3));
        $("#maphelptxt").height((tblHgt*.3));
        coordList.css("max-width:"+coordList.width()+"px;")

        var inputGml = $("#inputGml");
        inputGml.height((tblHgt*.3));
        inputGml.css("max-width:"+inputGml.width()+"px;")
    }
}
