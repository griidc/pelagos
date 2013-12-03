	var wkt = new OpenLayers.Format.WKT();
	var mxml = new OpenLayers.Format.XML();
	var map,draw,modify,vlayer;
	var mapOptions, toolbarOptions;
	var mapdiv, toolbardiv;
	var defaultStyle, selectStyle, temporaryStyle;
	var defaultStyleMap;
	var lastBounds;
	
	var lon = -90, lat = 25, //Gulf of Mexico
	zoom = 4,
	epsg4326 = new OpenLayers.Projection('EPSG:4326'),
	epsg900913 = new OpenLayers.Projection('EPSG:900913');
	
	function initMap(DIV,Options)
	{
		mapDiv = "#"+DIV;
		mapOptions = Options;
		map = new OpenLayers.Map( 
		{
			div: DIV,
			projection: new OpenLayers.Projection('EPSG:900913'),
			displayProjection: new OpenLayers.Projection('EPSG:4326'),
			zoomDuration: 10,
			//allOverlays:true,
			//controls: [],
			eventListeners: {
				featureover: function(e) 
				{
					e.feature.renderIntent = "select";
					e.feature.layer.drawFeature(e.feature);
					//console.log("Map says: " + e.feature.id + " mouse over " + e.feature.layer.name);
					$(document).trigger('overFeature',{"featureID":e.feature.id,"attributes":e.feature.attributes});
				},
				featureout: function(e) 
				{
					e.feature.renderIntent = "default";
					e.feature.layer.drawFeature(e.feature);
					//console.log("Map says: " + e.feature.id + " mouse out " + e.feature.layer.name);
					$(document).trigger('outFeature',{"featureID":e.feature.id,"attributes":e.feature.attributes});
				},
				featureclick: function(e) 
				{
					console.log("Map says: " + e.feature.id + " clicked on " + e.feature.layer.name + " udi:" + e.feature.attributes["udi"]);
					$(document).trigger('clickFeature',{"featureID":e.feature.id,"attributes":e.feature.attributes});
				}
			}
		});
		
		if (Options.staticMap)
		{
			Controls = map.getControlsByClass('OpenLayers.Control.Navigation');
			Controls[0].destroy();
			
			Controls = map.getControlsByClass('OpenLayers.Control.Zoom');
			Controls[0].destroy();
			
			// map.addControl(new OpenLayers.Control.Navigation());
			// map.addControl(new OpenLayers.Control.TouchNavigation());
			// map.addControl(new OpenLayers.Control.Zoom());
			// map.addControl(new OpenLayers.Control.ArgParser());
			// map.addControl(new OpenLayers.Control.Attribution());
			
		}
		
		style = OpenLayers.Util.extend({}, OpenLayers.Feature.Vector.style["default"]);
		style.graphicZIndex = 1;
		style.fillOpacity = 0;
		style.strokeWidth = 2;
				
		defaultStyle = new OpenLayers.Style(style);
		
		selectStyle = new OpenLayers.Style(
		{
			//strokeColor: "blue",
			//strokeDashstyle: "dashdot",
			label: "${udi}",
			fillOpacity: 0.0,
			strokeWidth: 4,
			graphicZIndex: 2
		});
			
		defaultStyleMap = new OpenLayers.StyleMap(
		{
			"default": defaultStyle,
			"select": selectStyle
		});
		
		//addUniqueStyle();
		
		//addRule('a','a',defaultStyle);
		
		
		var google_hybrid = new OpenLayers.Layer.Google('Google Hybrid Map', 
		{
			type: google.maps.MapTypeId.HYBRID,
			numZoomLevels: 7, //max 11 on hybrid in ocean.
			sphericalMercator: true
		});
		
		vlayer = new OpenLayers.Layer.Vector("Datasets",{
			projection: new OpenLayers.Projection('EPSG:4326'),
			styleMap: defaultStyleMap,
			rendererOptions: {zIndexing: true},
			afterAdd: function() 
			{
				console.log('layer ready');
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
			})
		});
		
		flayer = new OpenLayers.Layer.Vector("Filter", {
			styleMap: filterStyles,
			rendererOptions: {zIndexing: true},
			displayInLayerSwitcher: false
		});
		
		filter = new OpenLayers.Control.DrawFeature(flayer, OpenLayers.Handler.Polygon);
		map.addControl(filter);
		
		map.addLayers([google_hybrid, vlayer, flayer]);
		
		draw = new OpenLayers.Control.DrawFeature(vlayer, OpenLayers.Handler.Polygon);
		map.addControl(draw);
		
		map.events.register('updatesize', map, function () {
			console.log('Window Resized');
			//map.zoomToExtent(lastBounds);
			lastBounds = map.getExtent();
			
		});
		
		map.events.register('preaddlayer', map, function () {
			//console.log('Adding something?');
		});
		
		checkAllowModify(false);
		
		vlayer.events.register('loadstart', vlayer, function () {
			console.log("loading");
		});
		
		vlayer.events.on({
			'beforefeaturemodified': function(event) {
				//console.log("Selected " + event.feature.id  + " for modification");
				$("#eraseTool").button("enable");
				if (typeof event.feature == 'object')
				{
					checkPolygon(event.feature.id);
				}
			},
			'afterfeaturemodified': function(event) {
				//console.log("Finished with " + event.feature.id);
				$("#eraseTool").button("disable");
				
			},
			'beforefeatureadded': function(event) {
				stopDrawing();
				checkAllowModify(true);
			},
			'featureadded': function(event) {
				checkPolygon(event.feature.id);
				checkOnlyOnePolygon();
				$(document).trigger('featureAdded',getCoordinateList(event.feature));
			},
			'loadend': function(event) {
				console.log('Done loading vlayer layer');
				map.updateSize();
				vlayer.redraw();
			},
			'loadstart': function(event) {
				console.log('Done Drawing?');
			}
		});
		
		flayer.events.on({
			beforefeatureadded: function(event) {
				var geo = event.feature.geometry;
				console.debug(wkt.write(event.feature));
				//return false;
				flayer.removeAllFeatures();
				filter.deactivate();
			}
		});
				
		map.setCenter(new OpenLayers.LonLat(lon, lat).transform('EPSG:4326', 'EPSG:900913'), zoom);
		
		//Add map selector for highlighting
		selectControl = new OpenLayers.Control.SelectFeature(vlayer);
		map.addControls([selectControl]);
		//selectControl.activate();
		
		console.log('done with map');
		$(document).trigger('imready');
		
		lastBounds = map.getExtent();
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
	
	function addUniqueStyle()
	{
		var lookup = {
			"137": {strokeColor: "#FF00FF"},
			"140": {strokeColor: "#00FF00"},
			"135": {strokeColor: "#FFFF00"},
			"134": {strokeColor: "#00FFFF"}
		}
		
		defaultStyleMap.addUniqueValueRules("default", "pid", lookup);
	}
	
	function addRule(attrName,attrValue,Style)
	{
		var newRule = new OpenLayers.Rule({
				// a rule contains an optional filter
				filter: new OpenLayers.Filter.Comparison({
					type: OpenLayers.Filter.Comparison.LIKE,
					property: "pid", // the "foo" feature attribute
					value: "137"
				}),
					// if a feature matches the above filter, use this symbolizer
				symbolizer: {
					strokeColor: "#FF9700",
					fillColor: "#FF9700"
				}
		});
		
		Style.addRules([newRule]);
	}
	
	function initToolbar(DIV,Options)
	{
		toolbarOptions = Options;
		
		toolbardiv = '#'+DIV;
		$(toolbardiv)
		.append('<img id="homeTool" src="includes/images/home.png">')
		.append('<img id="filterTool" src="includes/images/filter.png">')
		.append('<img id="drawTool" src="includes/images/paint.png">');
		
		$(toolbardiv).append('<span id="drawtools"></span>');

		$("#drawtools")
		.append('<img id="polygonTool" src="includes/images/polygon.png">')
		.append('<img id="lineTool" src="includes/images/line.png">')
		.append('<img id="circleTool" src="includes/images/circle.png">')
		.append('<img id="squareTool" src="includes/images/square.png">');
		
		$(toolbardiv)
		.append('<img id="eraseTool" src="includes/images/delete.png">')
		//.append('<img id="panTool" src="includes/images/pan.png">')
		.append('<img id="worldTool" src="includes/images/world.png">')
		.append('<img id="zoominTool" src="includes/images/zoomin.png">')
		.append('<img id="zoomoutTool" src="includes/images/zoomout.png">');
		
		if (toolbarOptions.showExit)
		{
			$(toolbardiv)
			.append('<img id="exitTool" src="includes/images/exit.png">');
		}
		
		$("#exitTool").button()
		.click(function() {
			window.close();
		});
		
		$("#drawtools").hide();
		
		$("#polygonTool").button();
		$("#lineTool").button();
		$("#circleTool").button();
		$("#squareTool").button();
		//$("#panTool").button();
		//$("#panTool").button();
		
		
		$("#homeTool").button()
		.click(function() {
			goHome();
		});
		
		$("#drawTool").button()
		.click(function() {
			if (draw.active)
			{
				stopDrawing();
				//$(this).attr("src","includes/images/draw.png");
			}
			else
			{
				startDrawing();
				//$(this).attr("src","includes/images/pan.png");
			}
		});
		
		$("#panTool").button()
		.click(function() {
			stopDrawing();
		});
		
		$("#filterTool").button()
		.click(function() {
			drawFilter();
		});
				
		$("#eraseTool").button()
		.click(function() {
			deleteSelected();
		});
		
		$("#worldTool").button()
		.click(function() {
			zoomToMaxExtent();
		});
		
		$("#zoominTool").button()
		.click(function() {
			zoomIn();
		});
		
		$("#zoomoutTool").button()
		.click(function() {
			zoomOut();
		});
		
		$("#eraseTool").button("disable");
		
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
		selectControl.highlight(myFeature);
	}
	
	function unhighlightFeature(attrName,attrValue)
	{
		var myFeature=vlayer.getFeaturesByAttribute(attrName,attrValue)[0];
		selectControl.unhighlight(myFeature);
	}
	
	function selectFeature(attrName,attrValue)
	{
		var myFeature=vlayer.getFeaturesByAttribute(attrName,attrValue)[0];
		selectControl.highlight(myFeature);
	}
	
	function unselectFeature(attrName,attrValue)
	{
		var myFeature=vlayer.getFeaturesByAttribute(attrName,attrValue)[0];
		selectControl.unselect(myFeature);
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
				$("#drawTool").button("disable");
				return true;
			}
			else
			{
				$("#drawTool").button("enable");
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
		$.ajax({
			url: "//proteus.tamucc.edu/~mvandeneijnden/map/geocheck.php", // replace this url with geoviz lib
			type: "POST",
			data: {wkt: WKT, featureid: featureID},
			context: document.body
			}).done(function(html) {
				$(document).trigger('featureChecked',html);
				console.log(html);
		});
	}
	
	function addFeatureFromWKT(WKT,Attributes,Style)
	{
		var addFeature = wkt.read(wktTransformToSperMerc(WKT));
		
		// Sample: {"strokeColor": "#ff00ff", "fillColor": "#ffffff"}
		if (typeof Style == 'object')
		{
			var style = OpenLayers.Util.extend({}, OpenLayers.Feature.Vector.style['default']);
			style.fillColor = Style.fillColor;
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
		for (var i=0;i<layer.features.length;i++)
		{
			var tFeature = tLayer.features[i];
			tFeature.geometry.transform(map.getProjectionObject(),'EPSG:4326');
		}
		return tLayer;
		
	}
	
	function getCoordinateList(Feature)
	{
		var myFeature = Feature.clone();
		myFeature = featureTransformToWGS84(myFeature);
		var pointList = myFeature.geometry.getVertices();
		var points = "";
		
		for (var i=0;i<pointList.length;i++)
		{
			points += pointList[i].y + ","+pointList[i].x+" ";
		}
		
		points += pointList[0].y + ","+pointList[0].x;
		return points;
		
	}
	
	function addFeatureFromcoordinateList(List,NoFlip)
	{
		var pointList = List.split(" ");
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
		console.debug(WKT);
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
			$("#drawtools").fadeIn();
			//$("#drawtools").show();
		}
		
		if (!checkOnlyOnePolygon())
		{
			draw.activate();
		}
	}
	
	function stopDrawing()
	{
		$("#drawtools").fadeOut();
		//$("#drawtools").hide();
		draw.deactivate();
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
		}
		checkOnlyOnePolygon();
	}
	
	