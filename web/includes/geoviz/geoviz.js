	var wkt = new OpenLayers.Format.WKT();
	var vlayer;
	
	var lon = -90,
	lat = 25,
	zoom = 6,
	epsg4326 = new OpenLayers.Projection('EPSG:4326'),
	epsg900913 = new OpenLayers.Projection('EPSG:900913');
	
	
	function initMap(DIV)
	{
		map = new OpenLayers.Map( 
		{
			div: DIV,
			projection: new OpenLayers.Projection('EPSG:900913'),
			displayProjection: new OpenLayers.Projection('EPSG:4326'),
			zoomDuration: 10,
			eventListeners: {
				featureover: function(e) 
				{
					e.feature.renderIntent = "select";
					e.feature.layer.drawFeature(e.feature);
					//console.log("Map says: " + e.feature.id + " mouse over " + e.feature.layer.name);
					//highlightRow(e);
				},
				featureout: function(e) 
				{
					e.feature.renderIntent = "default";
					e.feature.layer.drawFeature(e.feature);
					//console.log("Map says: " + e.feature.id + " mouse out " + e.feature.layer.name);
					//unhighlightRow(e);
				},
				featureclick: function(e) 
				{
					//console.log("Map says: " + e.feature.id + " clicked on " + e.feature.layer.name);
					//myfeature = e.feature;
					//myfeature.geometry.transform(epsg900913,epsg4326);   
					//console.log(wkt.write(myfeature));
				}
			}
			
		});
		
		var defaultStyleMap = new OpenLayers.StyleMap(
		{
			"default": new OpenLayers.Style(
			{
				strokeOpacity: 0.75,
				strokeWidth: 3,
				fillOpacity: 0,
				graphicZIndex: 1
			}),
			"select": new OpenLayers.Style(
			{
				strokeOpacity: 1,
				fillOpacity: .3,
				strokeWidth: 5,
				//label: "${udi}",
				fontColor: "white",
				labelOutlineColor: "black",
				labelOutlineWidth: 3,
				graphicZIndex: 2
			})
		});
		
		google_hybrid = new OpenLayers.Layer.Google('Google Hybrid Map', 
		{
			type: google.maps.MapTypeId.HYBRID,
			numZoomLevels: 11,
			sphericalMercator: true
		});
		
		vlayer = new OpenLayers.Layer.Vector("Datasets",{
			projection: new OpenLayers.Projection('EPSG:4326'),
			//styleMap: defaultStyleMap,
			displayInLayerSwitcher: false
		});
		
		map.addLayers([google_hybrid, vlayer]);
	
		map.events.register('updatesize', map, function () {
			console.log('Window Resised');
			//map.zoomToExtent(vlayer.features);
		});
		
		map.events.register('addlayer', map, function () {
			console.log('Ready?');
			//map.zoomToExtent(vlayer.features);
		});
		
		modify = new OpenLayers.Control.ModifyFeature(vlayer);
		modify.mode = OpenLayers.Control.ModifyFeature.RESHAPE;
		modify.createVertices = true;
		
		vlayer.events.register('loadstart', vlayer, function () {
			console.log("loading");
		});
		
		vlayer.events.on({
			'beforefeaturemodified': function(event) {
				//console.log("Selected " + event.feature.id  + " for modification");
				$("#eraseTool").button("enable");
			},
			'afterfeaturemodified': function(event) {
				//console.log("Finished with " + event.feature.id);
				$("#eraseTool").button("disable");
				checkPolygon(event.feature);
			},
			'beforefeatureadded': function(event) {
				draw.deactivate();
				modify.activate();
			},
			'featureadded': function(event) {
				checkPolygon(event.feature);
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
		
		draw = new OpenLayers.Control.DrawFeature(vlayer, OpenLayers.Handler.Polygon);
		map.addControl(draw);
		
		mousepost = new OpenLayers.Control.MousePosition()
		map.addControl(mousepost);
		
		//draw.activate();
		
		map.addControl(modify);
				
		map.setCenter(new OpenLayers.LonLat(lon, lat).transform('EPSG:4326', 'EPSG:900913'), zoom);
		
		//Add map selector for highlighting
		//selectControl = new OpenLayers.Control.SelectFeature(vlayer);
		//map.addControls([selectControl]);
		//selectControl.activate();
	}
	
	function checkPolygon(feature)
	{
		WKT = wkt.write(feature);
		$.ajax({
			url: "//proteus.tamucc.edu/~mvandeneijnden/test/check.php",
			type: "POST",
			data: {wkt: WKT},
			context: document.body
			}).done(function(html) {
			console.log(html);
		});
	}
	
	function addFeatureFromWKT(WKT, Attributes, Style)
	{
		var addFeature = wkt.read(wktTransformToSperMerc(WKT));
		
		// Sample: {'strokeColor': '#ff00ff, 'fillColor': '#ffffff'}
		if (Style != "")
		{
			addFeature.style = Style;
		}
		
		// Sample: {"attribute" : "value", "label": "text"}
		if (Attributes != "")
		{
			addFeature.attributes = Attributes;
		}
				
		vlayer.addFeatures(addFeature);
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
	
	function transformLayers(layer)
	{
		var tLayer = layer.clone();
		for (var i=0;i<layer.features.length;i++)
		{
			var tFeature = tLayer.features[i];
			tFeature.geometry.transform(map.getProjectionObject(),'EPSG:4326');
		}
		return tLayer;
		
	}
	
	function initToolbar(DIV)
	{
		var toolbardiv = '#'+DIV;
		$(toolbardiv)
		.append('<img id="homeTool" src="includes/images/home.png">')
		.append('<img id="drawTool" src="includes/images/draw.png">')
		
		.append('<img id="polygonTool" src="includes/images/polygon.png">')
		.append('<img id="lineTool" src="includes/images/line.png">')
		.append('<img id="circleTool" src="includes/images/circle.png">')
		.append('<img id="squareTool" src="includes/images/square.png">')
		
		
		.append('<img id="eraseTool" src="includes/images/delete.png">')
		.append('<img id="panTool" src="includes/images/pan.png">')
		.append('<img id="worldTool" src="includes/images/world.png">')
		.append('<img id="zoominTool" src="includes/images/zoomin.png">')
		.append('<img id="zoomoutTool" src="includes/images/zoomout.png">');
		
		$("#polygonTool").button();
		$("#lineTool").button();
		$("#circleTool").button();
		$("#squareTool").button();
		//$("#panTool").button();
		//$("#panTool").button();
		
		
		$("#homeTool").button()
		.click(function() {
			map.setCenter(new OpenLayers.LonLat(lon, lat).transform('EPSG:4326', 'EPSG:900913'), zoom);
		});
		
		$("#drawTool").button()
		.click(function() {
			if (draw.active)
			{
				draw.deactivate();
				//$(this).attr("src","includes/images/draw.png");
			}
			else
			{
				draw.activate();
				//$(this).attr("src","includes/images/pan.png");
			}
		});
		
		$("#panTool").button()
		.click(function() {
			draw.deactivate();
		});
				
		$("#eraseTool").button()
		.click(function() {
			if (modify.feature)
			{
				deleteFeatureID = modify.feature.id
				modify.unselectFeature();
				vlayer.removeFeatures(vlayer.getFeatureById(deleteFeatureID));
			}
		});
		
		$("#worldTool").button()
		.click(function() {
			map.zoomToMaxExtent();
		});
		
		$("#zoominTool").button()
		.click(function() {
			map.zoomIn();
		});
		
		$("#zoomoutTool").button()
		.click(function() {
			map.zoomOut();
		});
		
		$("#eraseTool").button("disable");
		
	}