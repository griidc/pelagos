	var wkt = new OpenLayers.Format.WKT();
	var mxml = new OpenLayers.Format.XML();
	var map,draw,modify,vlayer,google_hybrid,flayer;
	var mapOptions, toolbarOptions;
	var mapdiv, toolbardiv;
	var defaultStyle, selectStyle, temporaryStyle;
	var defaultStyleMap;
	var lastBounds;
	var firstLoad;
	
	var lon = -90, lat = 25, //Gulf of Mexico
	zoom = 4,
	epsg4326 = new OpenLayers.Projection('EPSG:4326'),
	epsg900913 = new OpenLayers.Projection('EPSG:900913');
				
	function initMap(DIV,Options)
	{
		googleZoomLevel = 11, //max 11 on hybrid in ocean.
		
		firstLoad = false;
		
		mapDiv = "#"+DIV;
		mapOptions = Options;
		map = new OpenLayers.Map( 
		{
			div: DIV,
			projection: new OpenLayers.Projection('EPSG:900913'),
			displayProjection: new OpenLayers.Projection('EPSG:4326'),
			zoomDuration: 10,
			maxResolution: "auto",
			maxExtent: new OpenLayers.Bounds(-180, -90, 180, 90),
			minResolution: "auto",
			//allOverlays:true,
			//controls: [],
			eventListeners: {
				featureover: function(e) 
				{
					e.feature.renderIntent = "select";
					e.feature.layer.drawFeature(e.feature);
					////console.log("Map says: " + e.feature.id + " mouse over " + e.feature.layer.name);
					jQuery(document).trigger('overFeature',{"featureID":e.feature.id,"attributes":e.feature.attributes});
				},
				featureout: function(e) 
				{
					e.feature.renderIntent = "default";
					e.feature.layer.drawFeature(e.feature);
					////console.log("Map says: " + e.feature.id + " mouse out " + e.feature.layer.name);
					jQuery(document).trigger('outFeature',{"featureID":e.feature.id,"attributes":e.feature.attributes});
				},
				featureclick: function(e) 
				{
					//console.log("Map says: " + e.feature.id + " clicked on " + e.feature.layer.name + " udi:" + e.feature.attributes["udi"]);
					jQuery(document).trigger('clickFeature',{"featureID":e.feature.id,"attributes":e.feature.attributes});
				}
			}
		});
		
		if (Options.staticMap)
		{
			
			makeStatic();
			
			
			googleZoomLevel = 7;
			
			// map.addControl(new OpenLayers.Control.Navigation());
			// map.addControl(new OpenLayers.Control.TouchNavigation());
			// map.addControl(new OpenLayers.Control.Zoom());
			// map.addControl(new OpenLayers.Control.ArgParser());
			// map.addControl(new OpenLayers.Control.Attribution());
			
		}
		
		dstyle = OpenLayers.Util.extend({}, OpenLayers.Feature.Vector.style["default"]);
		dstyle.graphicZIndex = 1;
		dstyle.fillOpacity = 0;
		dstyle.strokeOpacity = 0.5;
		dstyle.strokeWidth = 2;
		
		defaultStyle = new OpenLayers.Style(dstyle);
		
		sstyle = OpenLayers.Util.extend({}, OpenLayers.Feature.Vector.style[dstyle]);
		
		sstyle.fillOpacity = 0.0;
		sstyle.strokeWidth = 4;
		sstyle.strokeOpacity = 1.0;
		sstyle.graphicZIndex = 2;
		//sstyle.strokeColor = "#FFFFFF";
		
		if (Options.labelAttr)
		{
			sstyle.label = "${" + Options.labelAttr + "}";
			//console.log("label set");
		}
		
		tstyle = OpenLayers.Util.extend({}, OpenLayers.Feature.Vector.style["temporary"]);
		
		selectStyle = new OpenLayers.Style(sstyle);
		
		temporaryStyle = new OpenLayers.Style(tstyle);
		
		defaultStyleMap = new OpenLayers.StyleMap(
		{
			"default": defaultStyle,
			"select": selectStyle
		});
		
		google_hybrid = new OpenLayers.Layer.Google('Google Hybrid Map', 
		{
			type: google.maps.MapTypeId.HYBRID,
			numZoomLevels: googleZoomLevel,
			sphericalMercator: true,
			displayInLayerSwitcher: true
		});
		
		google_terain = new OpenLayers.Layer.Google('Google Terrain Map', 
		{
			type: google.maps.MapTypeId.TERRAIN,
			numZoomLevels: googleZoomLevel,
			sphericalMercator: true,
			displayInLayerSwitcher: true
		});
		
		vlayer = new OpenLayers.Layer.Vector("Datasets",{
			projection: new OpenLayers.Projection('EPSG:4326'),
			styleMap: defaultStyleMap,
			rendererOptions: {zIndexing: true},
			afterAdd: function() 
			{
				//console.log('layer ready');
				// if (typeof renderMe == 'function') { 
					// renderMe(); 
				// }
			},
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
		
		//TODO: if Options.BaseMapTerainDefault == true then add terain layer first.
		
		map.addLayers([google_hybrid, vlayer, flayer]);
		
		//map.addControl( new OpenLayers.Control.LayerSwitcher());
		
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
			'NEXRAD Base Reflectivity',
			'https://mesonet.agron.iastate.edu/cache/tile.py/',
			{layername      : 'nexrad-n0q-900913',
				service         : '1.0.0',
				type            : 'png',
				visibility      : true,
				getURL          : get_my_url,
			isBaseLayer     : false}
			);
			
			map.addLayers([n0q]);
		}
		
		draw = new OpenLayers.Control.DrawFeature(vlayer, OpenLayers.Handler.Polygon);
		map.addControl(draw);
		
		map.events.register('updatesize', map, function () {
			//console.log('Window Resized');
			setTimeout( function() { 
				lastBounds = map.getExtent()
				}, 200);
				if (lastBounds)
				{
					//map.zoomToExtent(lastBounds,true);
				}
			
		});
		
		map.events.register('preaddlayer', map, function () {
			////console.log('Adding something?');
		});
		
		checkAllowModify(false);
		
		vlayer.events.register('loadstart', vlayer, function () {
			//console.log("loading");
		});
		
		vlayer.events.on({
			'beforefeaturemodified': function(event) {
				////console.log("Selected " + event.feature.id  + " for modification");
				jQuery("#eraseTool").button("enable");
				if (typeof event.feature == 'object')
				{
					//checkPolygon(event.feature.id);
				}
				jQuery("#helptext").html('Modify Mode<br>(Drag points to modify feature)');
			},
			'afterfeaturemodified': function(event) {
				////console.log("Finished with " + event.feature.id);
				jQuery("#eraseTool").button("disable");
				checkOnlyOnePolygon();
				if (typeof event.feature == 'object')
				{
					jQuery(document).trigger('featureAdded',getCoordinateList(event.feature));
				}
				jQuery("#helptext").text('Navigation Mode');
				
			},
			'beforefeatureadded': function(event) {
				stopDrawing();
				checkAllowModify(true);
			},
			'featureadded': function(event) {
				//checkPolygon(event.feature.id);
				checkOnlyOnePolygon();
				jQuery(document).trigger('featureAdded',getCoordinateList(event.feature));
			},
			'loadend': function(event) {
				//console.log('Done loading vlayer layer');
				map.updateSize();
				vlayer.redraw();
			},
			'loadstart': function(event) {
				//console.log('Done Drawing?');
			}
		});
		
		flayer.events.on({
			beforefeatureadded: function(event) {
				//console.debug(wkt.write(event.feature));
				flayer.removeAllFeatures();
				filter.deactivate();
			},
			featureadded: function(event) {
				jQuery(document).trigger('filterDrawn');
			}
		});
		
		google.maps.event.addListener(google_hybrid.mapObject, "tilesloaded", function() {
			//console.log("Tiles loaded");
			if (!firstLoad)
			{
				//console.log('done with map');
				firstLoad = true;
				setTimeout( function() { 
					map.removeLayer(google_hybrid);
					map.updateSize();
					map.addLayer(google_hybrid);
						
					jQuery(document).trigger('imready');
				}
				, 100)
			};
		});
				
		map.setCenter(new OpenLayers.LonLat(lon, lat).transform('EPSG:4326', 'EPSG:900913'), zoom, true, true);
		map.render(DIV);
		
		//Add map selector for highlighting
		mapOptions.allowModify
		selectControl = new OpenLayers.Control.SelectFeature(vlayer);
		map.addControls([selectControl]);
		//selectControl.activate();
		
		lastBounds = map.getExtent();
	}
	
	function makeStatic()
	{
		Controls = map.getControlsByClass('OpenLayers.Control.Navigation');
		Controls[0].destroy();
		
		Controls = map.getControlsByClass('OpenLayers.Control.Zoom');
		Controls[0].destroy();
	}
	
	function showTerrainMap()
	{
		map.addLayers([google_terain]);
		//map.setBaseLayer(map.layers[1]);
		map.setBaseLayer(map.getLayersByName('Google Terrain Map'));
	}
	
	function showHybridMap()
	{
		map.setBaseLayer(map.layers[0]);
		//map.setBaseLayer(map.getLayersByName('Google Hybrid Map'));
	}
		
	function drawFilter()
	{
		filter.activate();
	}
	
	function getFilter()
	{
		return wktTransformToWGS84(wkt.write(flayer.features[0]));
	}
	
	function clearFilter()
	{
		flayer.removeAllFeatures();
	}
	
	function addImage(Img,Opacity)
	{
		var graphic = new OpenLayers.Layer.Image(
		'Image',
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
	
	function initToolbar(DIV,Options)
	{
		toolbarOptions = Options;
		
		toolbardiv = '#'+DIV;
		jQuery(toolbardiv)
		.append('<img id="homeTool" src="/images/geoviz/home.png">')
		//.append('<img id="filterTool" src="/images/geoviz/filter.png">')
		.append('<img id="drawTool" src="/images/geoviz/paint.png">');
		
		jQuery(toolbardiv).append('<span id="drawtools"></span>');

		jQuery("#drawtools")
		.append('<img id="polygonTool" src="/images/geoviz/polygon.png">')
		.append('<img id="lineTool" src="/images/geoviz/line.png">')
		.append('<img id="circleTool" src="/images/geoviz/circle.png">')
		.append('<img id="squareTool" src="/images/geoviz/square.png">');
		
		jQuery(toolbardiv)
		.append('<img id="eraseTool" src="/images/geoviz/delete.png">')
		//.append('<img id="panTool" src="/images/geoviz/pan.png">')
		.append('<img id="worldTool" src="/images/geoviz/world.png">')
		.append('<img id="zoominTool" src="/images/geoviz/zoomin.png">')
		.append('<img id="zoomoutTool" src="/images/geoviz/zoomout.png">');
		
		if (toolbarOptions.showExit)
		{
			jQuery(toolbardiv)
			.append('<img id="exitTool" src="/images/geoviz/exit.png">');
		}
		
		jQuery("#exitTool").button()
		.click(function() {
			stopDrawing();
			closeMe();
			}).qtip({
			content: {
				text: 'Exit the Map'
			}
		})
		
		jQuery("#drawtools").hide();
		
		jQuery("#homeTool")
		
		jQuery("#polygonTool").button();
		jQuery("#lineTool").button();
		jQuery("#circleTool").button();
		jQuery("#squareTool").button();
		
		jQuery("#homeTool").button()
		.click(function() {
			goHome();
			}).qtip({
			content: {
				text: 'Go Home'
			}
		});
		
		jQuery("#drawTool").button().qtip({content: {text: 'Draw a Polygon'}})
		.click(function() {
			if (draw.active)
			{
				stopDrawing();
				//$(this).attr("src","/images/geoviz/draw.png");
			}
			else
			{
				startDrawing();
				//$(this).attr("src","/images/geoviz/pan.png");
			}
		});
		
		jQuery("#panTool").button()
		.click(function() {
			stopDrawing();
		});
		
		jQuery("#filterTool").button()
		.click(function() {
			drawFilter();
		});
				
		jQuery("#eraseTool").button()
		.click(function() {
			deleteSelected();
		}).qtip({content: {text: 'Delete a Feature'}});
		
		jQuery("#worldTool").button().qtip({content: {text: 'Maximum Zoom Out'}})
		.click(function() {
			zoomToMaxExtent();
		});
		
		jQuery("#zoominTool").button().qtip({content: {text: 'Zoom In'}})
		.click(function() {
			zoomIn();
		});
		
		jQuery("#zoomoutTool").button().qtip({content: {text: 'Zoom Out'}})
		.click(function() {
			zoomOut();
		});
		
		jQuery("#eraseTool").button("disable");
		
		jQuery(toolbardiv).append('<span style="font-family:Arial, Verdana, sans-serif;text-align:right;float:right;font-size:20;" id="helptext"></span>');
		
		jQuery("#helptext").text('Navigation Mode');
		
	}
	
	//TODO: Zoom/Pan/Select/Highlight Feature Function
		
	function gotoAllFeatures()
	{
		map.zoomToExtent(vlayer.getDataExtent());
	}
	
	function gotoFeature(attrName,attrValue)
	{
		var myFeature=vlayer.getFeaturesByAttribute(attrName,attrValue)[0];
		map.zoomToExtent(myFeature.geometry.getBounds())
	}
	
	function highlightFeature(attrName,attrValue)
	{
		var myFeature=vlayer.getFeaturesByAttribute(attrName,attrValue)[0];
		if (myFeature)
		{
			selectControl.highlight(myFeature);
		}
	}
	
	function unhighlightFeature(attrName,attrValue)
	{
		var myFeature=vlayer.getFeaturesByAttribute(attrName,attrValue)[0];
		if (myFeature)
		{
			selectControl.unhighlight(myFeature);
		}
	}
	
	function selectFeature(attrName,attrValue)
	{
		var myFeature=vlayer.getFeaturesByAttribute(attrName,attrValue)[0];
		if (myFeature)
		{
			selectControl.highlight(myFeature);
		}
	}
	
	function unselectFeature(attrName,attrValue)
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
	
	function getFeatureById(FeatureID)
	{
		return Feature = vlayer.getFeatureById(FeatureID);
	}
	
	function getFeatureIDFromAttr(attrName,attrValue)
	{
		var myFeature=vlayer.getFeaturesByAttribute(attrName,attrValue)[0];
		return myFeature.id;
	}
	
	function checkPolygon(FeatureID)
	{
		var Feature = vlayer.getFeatureById(FeatureID);
		var featureID = Feature.id;
		var WKT = wkt.write(Feature);
		jQuery.ajax({
			url: "/includes/geoviz/geocheck.php", // replace this url with geoviz lib
			type: "POST",
			data: {wkt: WKT, featureid: featureID},
			context: document.body
			}).done(function(html) {
				jQuery(document).trigger('featureChecked',html);
				////console.log(html);
				return html;
		});
	}
	
	function addFeatureFromWKT(WKT,Attributes,Style)
	{
		var addFeature = wkt.read(wktTransformToSperMerc(WKT));
		
		// Sample: {"strokeColor": "#ff00ff", "fillColor": "#ffffff"}
		if (typeof Style == 'object')
		{
			var style = OpenLayers.Util.extend({}, OpenLayers.Feature.Vector.style['default']);
			//style.fillColor = Style.fillColor;
			//style.fillOpacity = Style.fillOpacity;
			//style.strokeColor = Style.strokeColor;
			//style.strokeWidth = Style.strokeWidth;
			//style.strokeOpacity = Style.strokeOpacity;
			
			//addFeature.style = style;
		}
		
		// Sample: {"attribute" : "value", "label": "text"}
		if (typeof Attributes == 'object')
		{
			addFeature.attributes = Attributes;
		}
				
		vlayer.addFeatures(addFeature);
	}
	
	function featureTransformToWGS84(Feature)
	{
		var myFeature = Feature.clone();
		myFeature.geometry.transform(map.getProjectionObject(),'EPSG:4326');
		return myFeature;
	}
	
	function featureTransformToSperMerc(Feature)
	{
		var myFeature = Feature.clone();
		myFeature.geometry.transform('EPSG:4326',map.getProjectionObject());
		return myFeature;
	}
	
	function wktTransformToWGS84(WKT)
	{
		var wktFeature = wkt.read(WKT);
		wktFeature.geometry.transform(map.getProjectionObject(),'EPSG:4326');
		return wkt.write(wktFeature);
	}
	
	function wktTransformToSperMerc(WKT)
	{
		var wktFeature = wkt.read(WKT);
		wktFeature.geometry.transform('EPSG:4326',map.getProjectionObject());
		return wkt.write(wktFeature);
	}
	
	function transformLayers(Layer)
	{
		var tLayer = Layer.clone();
		for (var i=0;i<tLayer.features.length;i++)
		{
			var tFeature = tLayer.features[i];
			tFeature.geometry.transform(map.getProjectionObject(),'EPSG:4326');
		}
		return tLayer;
		
	}
	
	function unhighlightAll()
	{
		for (var i=0;i<vlayer.features.length;i++)
		{
			var Feature = vlayer.features[i];
			selectControl.unhighlight(Feature);
		}
	}
	
	function getCoordinateList(Feature)
	{
		var myFeature = Feature.clone();
		myFeature = featureTransformToWGS84(myFeature);
		var pointList = myFeature.geometry.getVertices();
		var points = "";
		
		for (var i=0;i<pointList.length;i++)
		{
			points += pointList[i].y.toPrecision(8) + ","+pointList[i].x.toPrecision(8)+" ";
		}
		
		points += pointList[0].y.toPrecision(8) + ","+pointList[0].x.toPrecision(8);
		return points;
		
	}
	
	function checkPointList(List)
	{
		var pointList = "";
		var points = List.match(/(-?\d+\.\d+|-?\d+)/g); //-?\d+(\.\d+)?
		for (var i=0;i<points.length;i+=2)
		{
			if (i!=0) {pointList += " "};
			pointList += points[i] + "," + points[i+1];
		}
		return pointList;
	}
	
	function addFeatureFromcoordinateList(List,NoFlip)
	{
		
		var pointList = checkPointList(List);
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
		var WKT = "POLYGON((" + points.substring(0,(points.length)-1) + "))";
		//console.debug(WKT);
		var Feature = wkt.read(wktTransformToSperMerc(WKT));
		modify.activate();
		vlayer.addFeatures([Feature]);
	}
	
	function removeAllFeaturesFromMap()
	{
		vlayer.removeAllFeatures();
	}
	
	function startDrawing()
	{
		if (toolbarOptions.showDrawTools)
		{
			jQuery("#drawtools").fadeIn();
			//$("#drawtools").show();
		}
		
		if (!checkOnlyOnePolygon())
		{
			checkAllowModify(true);
			draw.activate();
			jQuery("#helptext").html('Drawing Mode<br>(Double click to stop)');
			
		}
	}
	
	function stopDrawing()
	{
		jQuery("#drawtools").fadeOut();
		//$("#drawtools").hide();
		modify.deactivate();
		draw.deactivate();
		filter.deactivate();
		jQuery("#helptext").text('Navigation Mode');
	}
	
	function goHome()
	{
		map.setCenter(new OpenLayers.LonLat(lon, lat).transform('EPSG:4326', 'EPSG:900913'), zoom);
	}	
	
	function zoomToMaxExtent()
	{
		map.zoomToMaxExtent()
	}
	
	function gotoAllFeatures()
	{
		map.zoomToExtent(vlayer.getDataExtent());
	}
	
	function zoomIn()
	{
		map.zoomIn();
	}
	
	function zoomOut()
	{
		map.zoomOut();
	}
	
	function deleteSelected()
	{
		if (modify.feature)
		{
			deleteFeatureID = modify.feature.id
			modify.unselectFeature();
			vlayer.removeFeatures(vlayer.getFeatureById(deleteFeatureID));
			jQuery(document).trigger('featureAdded','');
		}
		checkOnlyOnePolygon();
	}
	
	 function closeMe()
	 {
		jQuery(document).trigger('closeMe');
	 }
