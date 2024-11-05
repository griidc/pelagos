function GeoViz()
{
    var map,modify,vlayer,google_hybrid,flayer;
    var mapOptions, toolbarOptions;
    var drawControls;

    var defaultStyle, selectStyle, temporaryStyle;
    var defaultStyleMap;
    var lastBounds;
    var firstLoad;
    var drawMode = "polygon";

    this.drawMode = drawMode;

    var mapDiv;
    this.mapDiv = mapDiv;

    this.toolbardiv;

    this.orderEnum =
    {
        EMPTY: -1,
        OK : 0,
        LONGLAT : 1,
        LATLONG : 2,
        UNKNOWN : 3,
        MIXED: 4,
        LATLONGML : 5,
        LONGLATML : 6
    }

    this.wkt = new OpenLayers.Format.WKT();
    this.gml = new OpenLayers.Format.GML.v3({
             srsName: "urn:x-ogc:def:crs:EPSG:4326"
         });

    var lon = -90.5, lat = 25, //Gulf of Mexico
    zoom = 4,
    epsg4326 = new OpenLayers.Projection("EPSG:4326"),
    epsg900913 = new OpenLayers.Projection("EPSG:900913");

    this.initMap = function(DIV,Options)
    {
        googleZoomLevel = 21, //max 11 on hybrid in ocean. //max 21

        firstLoad = false;

        mapDiv = "#"+DIV;

        mapOptions = Options;

        google_hybrid = new OpenLayers.Layer.Google("Google Hybrid Map",
        {
            type: google.maps.MapTypeId.SATELLITE,
            numZoomLevels: googleZoomLevel,
            sphericalMercator: true,
            displayInLayerSwitcher: true
        });

        google_sat = new OpenLayers.Layer.Google("Google BaseMap",
        {
            type: google.maps.MapTypeId.SATELLITE,
            numZoomLevels: 22,
            sphericalMercator: true,
            displayInLayerSwitcher: true
        });

        var usgsTopoBaseMap = new OpenLayers.Layer.XYZ(
            'test',
            'https://basemap.nationalmap.gov/arcgis/rest/services/USGSImageryTopo/MapServer/tile/${z}/${y}/${x}',
            {
                maxZoom: 20,
                sphericalMercator: true,
                isBaseLayer: true,
                attribution: 'Tiles courtesy of the <a href="https://usgs.gov/">U.S. Geological Survey</a>',
            }
        );

        var osm = new OpenLayers.Layer.OSM();

        map = new OpenLayers.Map(
        {
            layers: [usgsTopoBaseMap],
            center: new OpenLayers.LonLat(lon, lat).transform("EPSG:4326", "EPSG:900913"),
            zoom: zoom,
            div: DIV,
            projection: new OpenLayers.Projection("EPSG:900913"),
            displayProjection: new OpenLayers.Projection("EPSG:4326"),
            zoomDuration: 20,
            maxResolution: "auto",
            maxExtent: new OpenLayers.Bounds(-180, -90, 180, 90),
            minResolution: "auto",
            eventListeners: {
                featureover: function(e)
                {
                    e.feature.renderIntent = "select";
                    e.feature.layer.drawFeature(e.feature);
                    jQuery(mapDiv).trigger("overFeature",{"featureID":e.feature.id,"attributes":e.feature.attributes});
                },
                featureout: function(e)
                {
                    e.feature.renderIntent = "default";
                    e.feature.layer.drawFeature(e.feature);
                    jQuery(mapDiv).trigger("outFeature",{"featureID":e.feature.id,"attributes":e.feature.attributes});
                },
                featureclick: function(e)
                {
                    jQuery(mapDiv).trigger("clickFeature",{"featureID":e.feature.id,"attributes":e.feature.attributes});
                }
            }
        });

        if (Options.staticMap)
        {
            this.makeStatic();
            googleZoomLevel = 7;
        }

        dstyle = OpenLayers.Util.extend({}, OpenLayers.Feature.Vector.style["default"]);
        dstyle.graphicZIndex = 1;
        dstyle.fillOpacity = 0.0;
        dstyle.strokeWidth = 4;
        dstyle.strokeOpacity = 1.0;
        dstyle.pointRadius = 12;

        defaultStyle = new OpenLayers.Style(dstyle);

        sstyle = OpenLayers.Util.extend({}, OpenLayers.Feature.Vector.style[dstyle]);

        sstyle.fillOpacity = 0.0;
        sstyle.strokeWidth = 4;
        sstyle.strokeOpacity = 1.0;
        sstyle.pointRadius = 12;

        if (Options.labelAttr)
        {
            sstyle.label = "${" + Options.labelAttr + "}";
            sstyle.labelAlign = "cm";
        }

        tstyle = OpenLayers.Util.extend({}, OpenLayers.Feature.Vector.style["temporary"]);

        selectStyle = new OpenLayers.Style(sstyle);

        temporaryStyle = new OpenLayers.Style(tstyle);

        defaultStyleMap = new OpenLayers.StyleMap(
        {
            "default": defaultStyle,
            "select": selectStyle
        });


        google_terain = new OpenLayers.Layer.Google("Google Terrain Map",
        {
            type: google.maps.MapTypeId.TERRAIN,
            numZoomLevels: googleZoomLevel,
            sphericalMercator: true,
            displayInLayerSwitcher: true
        });

        vlayer = new OpenLayers.Layer.Vector("Datasets",{
            projection: new OpenLayers.Projection("EPSG:4326"),
            styleMap: defaultStyleMap,
            rendererOptions: {zIndexing: true},
            displayInLayerSwitcher: false
        });

        modify = new OpenLayers.Control.ModifyFeature(vlayer);
        modify.mode = OpenLayers.Control.ModifyFeature.RESHAPE;
        modify.createVertices = true;
        map.addControl(modify);

        var filterStyles = new OpenLayers.StyleMap(
        {
            "default": new OpenLayers.Style(
            {
                strokeColor: "#66CCCC",
                strokeOpacity: 1,
                strokeWidth: 3,
                fillOpacity: 0.0,
                fillColor: "#66CCCC",
                strokeDashstyle: "dash",
                label: "FILTER AREA",
                fontColor: "white",
                labelOutlineColor: "black",
                labelOutlineOpacity: 1,
                fontOpacity: 1,
                labelOutlineWidth: .5,
                graphicZIndex: -2
            }),
            "select": new OpenLayers.Style(
            {
                strokeColor: "#66CCCC",
                strokeOpacity: 1,
                strokeWidth: 3,
                fillOpacity: 0.0,
                fillColor: "#66CCCC",
                strokeDashstyle: "dash",
                label: "FILTER AREA",
                fontColor: "white",
                labelOutlineColor: "black",
                labelOutlineOpacity: 1,
                fontOpacity: 1,
                labelOutlineWidth: .5,
                graphicZIndex: -2
            })
        });

        flayer = new OpenLayers.Layer.Vector("Filter", {
            styleMap: filterStyles,
            rendererOptions: {zIndexing: true},
            displayInLayerSwitcher: false
        });

        filter = new OpenLayers.Control.DrawFeature(flayer, OpenLayers.Handler.RegularPolygon, {
                            handlerOptions: {
                                sides: 4,
                                irregular: true
                            }
            });
        map.addControl(filter);

        map.addLayers([vlayer, flayer]);

        map.events.register("click", map , function(e){
            var vpxy = map.getLayerPxFromViewPortPx(e.xy) ;
            jQuery(mapDiv).trigger("mapClick",vpxy);
        });

        function get_my_url (bounds) {
            var res = this.map.getResolution();
            var x = Math.round ((bounds.left - this.maxExtent.left) / (res * this.tileSize.w));
            var y = Math.round ((this.maxExtent.top - bounds.top) / (res * this.tileSize.h));
            var z = this.map.getZoom();

            var path = z + "/" + x + "/" + y + "." + this.type +"?"+ parseInt(Math.random()*9999);
            var url = this.url;
            if (url instanceof Array) {
                url = this.selectUrl(path, url);
            }
            return url + this.service +"/"+ this.layername +"/"+ path;

        }

        if (Options.showRadar)
        {
            var n0q = new OpenLayers.Layer.TMS(
            "NEXRAD Base Reflectivity",
            "https://mesonet.agron.iastate.edu/cache/tile.py/",
            {layername      : "nexrad-n0q-900913",
                service         : "1.0.0",
                type            : "png",
                visibility      : true,
                getURL          : get_my_url,
            isBaseLayer     : false}
            );

            map.addLayers([n0q]);
        }

        drawControls = {
            point: new OpenLayers.Control.DrawFeature(vlayer,OpenLayers.Handler.Point),
            line: new OpenLayers.Control.DrawFeature(vlayer,OpenLayers.Handler.Path),
            polygon: new OpenLayers.Control.DrawFeature(vlayer,OpenLayers.Handler.Polygon),
            box: new OpenLayers.Control.DrawFeature(vlayer,OpenLayers.Handler.RegularPolygon, {
                handlerOptions: {
                    sides: 4,
                    irregular: true
                }
            }
            )
        };

        for(var key in drawControls) {
            map.addControl(drawControls[key]);
        }

        this.setDrawMode("polygon");

        map.events.register("updatesize", map, function () {
            setTimeout(function () {
                lastBounds = map.getExtent()
                }, 200);
                if (lastBounds)
                {
                    // May need this because resizing screen can mess up zoom level.
                    //map.zoomToExtent(lastBounds,true);
                }
        });

        checkAllowModify(false);

        vlayer.events.on({
            "beforefeaturemodified": function(event) {
                jQuery(mapDiv).trigger("modeChange","Modify");
            },
            "afterfeaturemodified": function(event) {
                checkOnlyOnePolygon();
                if (typeof event.feature == "object")
                {
                    jQuery(mapDiv).trigger("featureAdded",getCoordinateList(event.feature));
                }
                jQuery(mapDiv).trigger("modeChange","Navigation");
            },
            "beforefeatureadded": function(event) {
                stopDrawing();
                checkAllowModify(true);
            },
            "sketchstarted": function(event) {
                jQuery(mapDiv).trigger("modeChange","Drawing");
            },
            "sketchcomplete": function(event) {
                jQuery(mapDiv).trigger("modeChange","Navigation");
            },
            "featureadded": function(event) {
                checkOnlyOnePolygon();
                jQuery(mapDiv).trigger("featureAdded",getCoordinateList(event.feature));
            },
            "loadend": function(event) {
                map.updateSize();
                vlayer.redraw();
            },
            "vertexmodified": function(event) {
                jQuery(mapDiv).trigger("vectorChanged",getCoordinateList(event.feature));
            },
            "sketchmodified": function(event) {
                jQuery(mapDiv).trigger("vectorChanged",getCoordinateList(event.feature));
            }
        });

        flayer.events.on({
            beforefeatureadded: function(event) {
                flayer.removeAllFeatures();
                filter.deactivate();
            },
            featureadded: function(event) {
                jQuery(mapDiv).trigger("filterDrawn");
            }
        });

        // google.maps.event.addListener(google_hybrid.mapObject, "tilesloaded", function() {
        //     google.maps.event.clearListeners(google_hybrid.mapObject, "tilesloaded");
        //     google.maps.event.addListener(google_hybrid.mapObject, "idle", function() {
        //         setTimeout(function () {
        //             // Hotfix to allow Hybrid map being loaded with New Google API (15 Feb 2016)
        //             map.setBaseLayer(google_hybrid);
                    jQuery(mapDiv).trigger("imready",mapDiv);
        //             }
        //         , 100);
        //         google.maps.event.clearListeners(google_hybrid.mapObject, "idle");
        //     });
        // });

        //Add map selector for highlighting
        mapOptions.allowModify
        selectControl = new OpenLayers.Control.SelectFeature(vlayer);
        map.addControls([selectControl]);
        lastBounds = map.getExtent();
    }

    this.flashMap = function ()
    {
        // setTimeout(function () {
        //     map.removeLayer(google_hybrid);
        //     map.updateSize();
        //     map.addLayer(google_hybrid);
        //     map.updateSize();
        // }
        // , 100)
    }

    this.setDrawMode = function(handlerType)
    {
        for(key in drawControls) {
            var control = drawControls[key];
            if (handlerType == key)
            {
                this.drawMode = handlerType;
                drawMode = handlerType;
                control.deactivate();
            }
            else
            {
                control.deactivate();
            }
        }
    }

    this.updateMap = function ()
    {
        map.updateSize();
    }

    this.makeStatic = function ()
    {
        Controls = map.getControlsByClass("OpenLayers.Control.Navigation");
        if (Controls.length > 0)
        {Controls[0].destroy();}

        Controls = map.getControlsByClass("OpenLayers.Control.Zoom");
        if (Controls.length > 0)
        {Controls[0].destroy();}
    }

    this.showTerrainMap = function ()
    {
        map.addLayers([google_terain]);
        map.setBaseLayer(map.getLayersByName("Google Terrain Map"));
    }

    this.showHybridMap = function ()
    {
        map.setBaseLayer(map.getLayersByName("Google Hybrid Map"));
    }

    this.drawFilter = function ()
    {
        filter.activate();
    }

    this.getFilter = function ()
    {
        return this.wktTransformToWGS84(this.wkt.write(flayer.features[0]));
    }

    this.clearFilter = function ()
    {
        flayer.removeAllFeatures();
    }

    this.addImage = function (Img,Opacity)
    {
        var graphic = new OpenLayers.Layer.Image(
        "Image",
        Img,
        map.getExtent(),
        new OpenLayers.Size(0,0),
            {
            isBaseLayer:false,
            visibility:true,
            opacity: Opacity
            }
        );
        map.addLayers([graphic]);
    }

    this.removeImage = function ()
    {
        var graphic = map.getLayersByName("Image");
        for (var i=0;i<graphic.length;i++)
        {
            map.removeLayer(graphic[i]);
        }
    }

    this.gotoAllFeatures = function ()
    {
        if (vlayer.features.length > 0)
        {
            var featureZoomLevel = map.getZoomForExtent(vlayer.getDataExtent());
            if (featureZoomLevel >= 8) {featureZoomLevel = 8;}
            featureZoomLevel = map.adjustZoom(featureZoomLevel);
            map.zoomToExtent(vlayer.getDataExtent());
            map.zoomTo(featureZoomLevel);
        }
    }

    this.gotoFeature = function (attrName,attrValue)
    {
        var myFeature=vlayer.getFeaturesByAttribute(attrName,attrValue)[0];
        var featureZoomLevel = map.getZoomForExtent(myFeature.geometry.getBounds());
        if (featureZoomLevel >= 8) {featureZoomLevel = 8;}
        featureZoomLevel = map.adjustZoom(featureZoomLevel);
        map.zoomToExtent(myFeature.geometry.getBounds())
        map.zoomTo(featureZoomLevel);
    }

    this.highlightFeature = function (attrName,attrValue)
    {
        var myFeature=vlayer.getFeaturesByAttribute(attrName,attrValue)[0];
        if (myFeature)
        {
            selectControl.highlight(myFeature);
        }
    }

    this.unhighlightFeature = function (attrName,attrValue)
    {
        var myFeature=vlayer.getFeaturesByAttribute(attrName,attrValue)[0];
        if (myFeature)
        {
            selectControl.unhighlight(myFeature);
        }
    }

    this.selectFeature = function (attrName,attrValue)
    {
        var myFeature=vlayer.getFeaturesByAttribute(attrName,attrValue)[0];
        if (myFeature)
        {
            selectControl.highlight(myFeature);
        }
    }

    this.selectNone = function ()
    {
        selectControl.unselectAll();
    }

    this.selectNone = function unselectFeature(attrName,attrValue)
    {
        var myFeature=vlayer.getFeaturesByAttribute(attrName,attrValue)[0];
        if (myFeature)
        {
            selectControl.unselect(myFeature);
        }
    }

    function checkAllowModify(On)
    {
        if (mapOptions.allowModify && On)
        {
            modify.activate();
        }
        else
        {
            modify.deactivate();
        }
    }

    this.canDraw = function ()
    {
        return checkOnlyOnePolygon();
    }

    function checkOnlyOnePolygon()
    {
        if (mapOptions.onlyOneFeature)
        {
            if (vlayer.features.length > 0)
            {
                jQuery("#drawTool").button("disable");
                return true;
            }
            else
            {
                jQuery("#drawTool").button("enable");
                return false;
            }
        }
    }

    this.getFeatureById = function (FeatureID)
    {
        return Feature = vlayer.getFeatureById(FeatureID);
    }

    this.getFeatureIDFromAttr = function (attrName,attrValue)
    {
        var myFeature=vlayer.getFeaturesByAttribute(attrName,attrValue)[0];
        return myFeature.id;
    }

    this.gmlToWKT = function (GML)
    {
        if (GML) {
            return jQuery.ajax({
                url: Routing.generate("pelagos_app_gml_towkt"),
                type: "POST",
                context: document.body,
                data: {gml: GML},
                dataType: "json",
                timeout: 10000,
                converters:
                {
                    "text json": function(json)
                    {
                        data = JSON.parse(json);
                        return data.hasOwnProperty('wkt') ? data.wkt : data;
                    },
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    return $.Deferred().reject();
                },
            })
        } else {
            return $.Deferred().resolve(false);
        }
    }

    this.wktToGML = function (WKT)
    {
        return jQuery.ajax({
            url: Routing.generate("pelagos_app_gml_fromwkt"),
            type: "POST",
            data: {wkt: WKT},
            context: document.body,
            dataType: "json",
            timeout: 10000,
            converters:
            {
                "text json": function(json)
                {
                    data = JSON.parse(json);
                    return data.hasOwnProperty('gml') ? data.gml : data;
                },
            },
            error: function (jqXHR, textStatus, errorThrown) {
                return $.Deferred().reject();
            },
        })
    }

    this.addFeatureFromWKT = function (WKT,Attributes,Style)
    {
        if (WKT != "")
        {
            var addFeature = this.wkt.read(this.wktTransformToSperMerc(WKT));

            // Sample: {"strokeColor": "#ff00ff", "fillColor": "#ffffff"}
            if (typeof Style == "object")
            {
                var style = OpenLayers.Util.extend({}, OpenLayers.Feature.Vector.style["default"]);
                //style.fillColor = Style.fillColor;
                //style.fillOpacity = Style.fillOpacity;
                //style.strokeColor = Style.strokeColor;
                //style.strokeWidth = Style.strokeWidth;
                //style.strokeOpacity = Style.strokeOpacity;

                //addFeature.style = style;
            }

            // Sample: {"attribute" : "value", "label": "text"}
            if (typeof Attributes == "object")
            {
                addFeature.attributes = Attributes;
                for (i=0; i < addFeature.length; i++) {
                    addFeature[i].attributes = Attributes;
                }
            }

            try {
                vlayer.addFeatures(addFeature);
            }
            catch (err) {
                console.debug(err);
            }
        }

        return addFeature;
    }

    function featureTransformToWGS84(Feature)
    {
        var myFeature = Feature.clone();
        myFeature.geometry.transform(map.getProjectionObject(),"EPSG:4326");
        return myFeature;
    }

    function featureTransformToSperMerc(Feature)
    {
        var myFeature = Feature.clone();
        myFeature.geometry.transform("EPSG:4326",map.getProjectionObject());
        return myFeature;
    }

    this.wktTransformToWGS84 = function (WKT)
    {
        if (WKT != "")
        {
            var wktFeature = this.wkt.read(WKT);
            if (wktFeature.length == undefined) {
                wktFeature.geometry.transform(map.getProjectionObject(),"EPSG:4326")
            } else {
                $.each(wktFeature, function(index, feature) {
                    feature.geometry.transform(map.getProjectionObject(),"EPSG:4326");
                });
            }
            return this.wkt.write(wktFeature);
        }
    }

    this.wktTransformToSperMerc = function (WKT)
    {
        if (WKT != "")
        {
            var wktFeature = this.wkt.read(WKT);
            if (wktFeature.length == undefined) {
                wktFeature.geometry.transform("EPSG:4326",map.getProjectionObject());
            } else {
                $.each(wktFeature, function(index, feature) {
                    feature.geometry.transform("EPSG:4326",map.getProjectionObject());
                });
            }
            return this.wkt.write(wktFeature);
        }
    }

    function transformLayers(Layer)
    {
        var tLayer = Layer.clone();
        for (var i=0;i<tLayer.features.length;i++)
        {
            var tFeature = tLayer.features[i];
            tFeature.geometry.transform(map.getProjectionObject(),"EPSG:4326");
        }
        return tLayer;
    }

    this.unhighlightAll = function ()
    {
        for (var i=0;i<vlayer.features.length;i++)
        {
            var Feature = vlayer.features[i];
            selectControl.unhighlight(Feature);
        }
    }

    this.determineOrder = function (List)
    {
        var pointList = this.checkPointList(List);
        var orders = new Array();
        var LongLat = 0;
        var LatLong = 0;

        if (pointList == "")
        {
            return this.orderEnum.EMPTY;
        }

        pointList = pointList.split(" ");

        for (var i=0;i<pointList.length;i++)
        {
            var pointSplit = pointList[i].split(",");

            if (Math.abs(pointSplit[0]) > 90 && Math.abs(pointSplit[1]) <= 90)
            {
                orders.push("LongLat");
                LongLat += 1;
            }
            else if (Math.abs(pointSplit[0]) <= 90 && Math.abs(pointSplit[1]) > 90)
            {
                LatLong += 1;
            }
            else if (pointSplit[0] > pointSplit[1] && pointSplit[1] < 0 && (pointSplit[0] - (pointSplit[1])) > 90)
            {
                LatLong += .5;
            }
            else
            {
                LatLong += 0;
            }
        }

        if ((LatLong == pointList.length))
        {
            return this.orderEnum.LATLONG;
        }
        else if ((LatLong == pointList.length))
        {
            return this.orderEnum.LATLONG;
        }
        else if ((LongLat == pointList.length))
        {
            return this.orderEnum.LONGLAT;
        }
        else if (LongLat > 0 && LatLong > 0)
        {
            return this.orderEnum.MIXED;
        }
        else if ((LatLong / pointList.length) > (LongLat / pointList.length))
        {
            return this.orderEnum.LATLONGML;
        }
        else if ((LatLong / pointList.length) < (LongLat / pointList.length))
        {
            return this.orderEnum.LONGLATML;
        }
        else if (LongLat == 0 && LatLong == 0)
        {
            return this.orderEnum.UNKNOWN;
        }
    }

    function determineOrder2(List)
    {
        var pointList = checkPointList(List);
        var lx = new Array();
        var ry = new Array();

        pointList = pointList.split(" ");

        for (var i=0;i<pointList.length;i++)
        {
            var pointSplit = pointList[i].split(",");
            lx.push(pointSplit[0]);
            ry.push(pointSplit[1]);
        }
        lxMax = Math.max.apply(Math,lx);
        lxMin = Math.min.apply(Math,lx);
        ryMax = Math.max.apply(Math,ry);
        ryMin = Math.min.apply(Math,ry);

        if (Math.abs(lxMax) > 90 || Math.abs(lxMin) > 90)
        {
            return "LongLat";
        }
        else if (Math.abs(ryMax) > 90 || Math.abs(ryMin) > 90)
        {
            return "LatLong";
        }
        else
        {
            return "Unknown";
        }
    }

    this.checkCoordList = function (List)
    {
        var pointList = this.checkPointList(List);
        var lx = 0;
        var ry = 0;
        var msg = new Array();
        var msgTxt = "OK";

        var superList = pointList.trim();
        superList = superList.split(/[\s,]+/); // /^\s+(.*?)\s+$/

        pointList = pointList.split(" ");

        if (superList.length % 2 === 0)
        {
            for (var i=0;i<pointList.length;i++)
            {
                var pointSplit = pointList[i].split(",");
                lx = pointSplit[0];
                ry = pointSplit[1];

                if (Math.abs(lx) > 90 && Math.abs(ry) > 90)
                {
                    msgTxt = "Both coordinates over 90 in set "+(i+1);
                    msg.push(msgTxt);
                }

                if (Math.abs(lx) > 180 || Math.abs(ry) > 180)
                {
                    msgTxt = "Some coordinates over 180 in set "+(i+1);
                    msg.push(msgTxt);
                }
            }

            if (msgTxt == "OK")
            {
                msg.push(msgTxt);
            }
        }
        else
        {
            msgTxt = "Odd number of values. Coordinates must be in latitude longitude pairs, only "+superList.length+" coordinates found";
            msg.push(msgTxt);
        }

        return msg;
    }

    function getCoordinateList (Feature)
    {
        var points = "";

        if (typeof Feature == "object" && Feature != null)
        {
            var myFeature = Feature.clone();
            myFeature = featureTransformToWGS84(myFeature);
            var pointList = myFeature.geometry.getVertices();

            for (var i=0;i<pointList.length;i++)
            {
                points += pointList[i].y.toPrecision(8) + ","+pointList[i].x.toPrecision(8)+" ";
            }
        }
        return points;

    }

    this.getCoordinateList = function (FeatureID)
    {
        var Feature = vlayer.getFeatureById(FeatureID);
        return getCoordinateList(Feature);
    }

    this.checkPointList = function (List)
    {
        var pointList = "";
        if (List != "")
        {
            var points = List.match(/(-?\d+\.\d+|-?\d+)/g); //-?\d+(\.\d+)?
            points == null ? pointsLength = 0 : pointsLength = points.length;
            for (var i=0;i<pointsLength;i+=2)
            {
                if (i!=0) {pointList += " "};
                pointList += points[i];
                if (typeof points[i+1] !== "undefined")
                {
                    pointList += "," + points[i+1];
                }
            }
        }
        return pointList;
    }

    this.addFeatureFromcoordinateList = function (List,NoFlip)
    {
        var pointList = this.checkPointList(List);

        checkMsg = this.checkCoordList(List);
        if (checkMsg != "OK")
        {
            jQuery(mapDiv).trigger("coordinateError",checkMsg);
            return false;
        }
        else
        {
            pointList = pointList.split(" ");
            var points = "";
            for (var i=0;i<pointList.length;i++)
            {
                var pointSplit = pointList[i].split(",");
                if (!NoFlip)
                {
                    points += pointSplit[1]+" "+pointSplit[0]+",";
                }
                else
                {
                    points += pointSplit[0]+" "+pointSplit[1]+",";
                }
            }
            if (drawMode == "polygon" || drawMode == "box")
            {
                const numPairs = points.split(" ").length;
                if (numPairs < 3) {
                    checkMsg = "Not enough points pairs for a polygon! (Minimum of 3)";
                    jQuery(mapDiv).trigger("coordinateError", checkMsg);
                    return false;
                }
                var WKT = "POLYGON((" + points.substring(0,(points.length)-1) + "))";
            }
            else if (drawMode == "point")
            {
                var WKT = "MULTIPOINT(" + points.substring(0,(points.length)-1) + ")";
            }
            else if (drawMode == "line")
            {
                var WKT = "LINESTRING(" + points.substring(0,(points.length)-1) + ")";
            }
            var sMwkt = this.wktTransformToSperMerc(WKT);
            if (sMwkt.indexOf("NaN") == -1)
            {
                var Feature = this.wkt.read(this.wktTransformToSperMerc(WKT));
                vlayer.addFeatures([Feature]);
                modify.activate();
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    this.removeAllFeaturesFromMap = function ()
    {
        this.stopDrawing();
        if (typeof vlayer == "undefined")
        {
            return false;
        }
        vlayer.removeAllFeatures();
        map.updateSize();
        vlayer.removeAllFeatures();
        return true;
    }

    function startDrawing ()
    {
        if (typeof toolbarOptions != "undefined")
        {
            if (toolbarOptions.showDrawTools)
            {
                jQuery("#drawtools").fadeIn();
            }
        }

        if (!checkOnlyOnePolygon())
        {
            checkAllowModify(true);
            drawControls[drawMode].activate()
        }
    }

    this.startDrawing = function ()
    {
        startDrawing();
    }

    function stopDrawing ()
    {
        if (typeof modify == "undefined")
        {
            return false;
        }
        jQuery("#drawtools").fadeOut();
        modify.deactivate();
        drawControls[drawMode].deactivate();
        filter.deactivate();
        modify.activate();
        jQuery("#helptext").text("Navigation Mode");
        return true;
    }

    this.stopDrawing = function ()
    {
        stopDrawing ();
    }

    this.goHome = function ()
    {
        map.setCenter(new OpenLayers.LonLat(lon, lat).transform("EPSG:4326", "EPSG:900913"), zoom);
    }

    this.zoomToMaxExtent = function ()
    {
        map.zoomToMaxExtent()
    }

    this.panToFeature = function (FeatureID)
    {
        var Feature = vlayer.getFeatureById(FeatureID);
        map.panTo(Feature.geometry.getBounds().getCenterLonLat());
    }

    this.getSingleFeature = function ()
    {
        var Feature = vlayer.features[0];
        if (typeof Feature != "undefined")
        {
            return Feature.id;
        }
    }

    this.getSingleFeatureClass = function ()
    {
        if (typeof vlayer.features[0] != "undefined")
        {
            var ClassName = vlayer.features[0].geometry.CLASS_NAME;
            if (typeof ClassName != "undefined")
            {
                return ClassName.split(".")[2];
            }
        }
        else
        { return false; }
    }

    this.zoomIn = function ()
    {
        map.zoomIn();
    }

    this.zoomOut = function ()
    {
        map.zoomOut();
    }

    this.deleteSelected = function ()
    {
        if (modify.feature)
        {
            deleteFeatureID = modify.feature.id
            modify.unselectFeature();
            vlayer.removeFeatures(vlayer.getFeatureById(deleteFeatureID));
            jQuery(mapDiv).trigger("featureAdded","");
        }
        checkOnlyOnePolygon();
    }

    function closeMe()
    {
        coordlist = this.getCoordinateList(vlayer.features[0]);
        jQuery(mapDiv).trigger("closeMe",coordlist);
    }

    function writeGML(Feature)
    {
        Feature.geometry.transform("EPSG:900913","EPSG:4326");
        return gml.write(Feature)

    }

    this.getWKT = function (FeatureID)
    {
        var Feature = vlayer.getFeatureById(FeatureID);
        if (typeof Feature == "object" && Feature != null)
        {
            return this.wkt.write(Feature);
        }
        else
        { return false; }
    }

    this.getWktFromFeatures = function ()
    {
        if (this.hasMultiFeatures() === true) {
            var Features = vlayer.features;
        }
        else {
            var Features = vlayer.features[0];
        }
        if (typeof Features == "object" && null !== Features )
        {
            return this.wkt.write(Features);
        }
        else
        { return false; }
    }

    this.getWKTFromBounds = function(left, bottom, right, top)
    {
        var bounds = new OpenLayers.Bounds.fromArray(Array(left, bottom, right, top));
        var myGeometry = bounds.toGeometry();
        var newFeature = new OpenLayers.Feature.Vector(myGeometry);
        var myWKT = this.wkt.write(newFeature);
        return myWKT;
    }

    this.getBBOX = function(FeatureID)
    {
        var myWKT = this.getWKT(FeatureID);

        var wgs84WKT = this.wktTransformToWGS84(myWKT);

        if (typeof wgs84WKT != "undefined")
        {
            var myFeature = this.wkt.read(wgs84WKT);

            if (typeof myFeature == "object" && myFeature != null)
            {
                var myBounds = myFeature.geometry.getBounds();

                return myBounds.toArray();
            }
            else
            { return false; }
        }
        else
        { return false; }
    }

    this.hasMultiFeatures = function()
    {
      if (vlayer.features.length > 1) {
          return true;
      }
      return false;
    }
}
