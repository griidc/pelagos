<?php include_once '/usr/local/share/GRIIDC/php/aliasIncludes.php';?>

<link type="text/css" rel="stylesheet" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
<link type="text/css" rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/qtip2/2.1.0/jquery.qtip.min.css" />

<link type="text/css" rel="stylesheet" href="includes/css/map.css" type="text/css">
<script type="text/javascript" src="//code.jquery.com/jquery-1.9.1.js"></script>
<script type="text/javascript" src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<script type="text/javascript" src="//maps.google.com/maps/api/js?v=3&sensor=false"></script>
<script type="text/javascript" src="includes/js/OpenLayers.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/qtip2/2.1.0/jquery.qtip.min.js"></script>

<script>
	var $ = jQuery.noConflict();
	
	//$("#branding").hide();
	
	var map, vector, google_hybrid, selectControl, draw, drawings;
	var R1,R2, Y1;
	var lon = -90,
	lat = 25,
	zoom = 6,
	epsg4326 = new OpenLayers.Projection('EPSG:4326'),
	epsg900913 = new OpenLayers.Projection('EPSG:900913');
	
	$(document).ready(function() 
	{
		
		initTB();
		
		init();
		
		$("#filtertxt").keyup(function() {
			applyFilter($(this).val());
		});
		
		loadQtip();
		
		resizeMap();
		
		$(window).resize(function() 
		{
			resizeMap();
		});
		
	});
	
	function resizeMap()
	{
		$('#olmap').height(0);
		h = $('#main').height() - $('#squeeze-wrapper').height() - 22;
		$('#olmap').height(h);
	}
	
	function applyFilter(text)
	{
		var vFilter = new OpenLayers.Filter.Comparison({
			type: OpenLayers.Filter.Comparison.LIKE,
			matchCase: false,
			property: "title",
			value: ".*" + text + '.*'
		});
		
		//vFilter.value2regex("*",".","!");
		
		//console.debug (vFilter.evaluate());
		
		
	vector.filter = vFilter;
	vector.refresh({force: true});
	vector.redraw();
	}
	
	function loadDetails(udi)
	{
		if ($('div[id="'+udi+'"]').has(".dataset_details:empty").length == 1)
		{
			$.ajax({
				url: "//proteus.tamucc.edu/data-discovery/dataset_details/"+udi,
				context: document.body
				}).done(function(html) {
				//$( this ).addClass( "done" );
				
				$('div[id="'+udi+'"] .dataset_details').html(html)
				.show()
				//.closest(".dsdetails #morearrow").attr("src","includes/images/093.png");
				.siblings(".minitoolbar").children(".minitool").children("#morearrow")
				.attr("src","includes/images/093.png");
				
				//$("div#"+udi).append('<p>'+html+'</p>');
				//gotoFeature(udi);
			});
		}
		else
		{
			if ($('div[id="'+udi+'"]').has(".dataset_details:hidden").length == 1)
			{
				$('div[id="'+udi+'"] > .dataset_details').show()
				.siblings(".minitoolbar").children(".minitool").children("#morearrow")
				.attr("src","includes/images/093.png");
				//gotoFeature(udi);
			}
			else
			{
				$('div[id="'+udi+'"] > .dataset_details').hide()
				.siblings(".minitoolbar").children(".minitool").children("#morearrow")
				.attr("src","includes/images/092.png");
				//gotoAllFeatures();
			}
		}
	}
	
	function showList(features)
	{
		$("#dsdata").empty();
		
		R1=0; R2=0; Y1=0;
		
		for (var i = 0; i < features.length; i++) 
		{
			var udi = features[i].attributes.udi;
			var title = features[i].attributes.title;
			html = '<div class="dsdetails"  id="'+udi+'">';
			//html += '<img src="/data-discovery/includes/images/download-package.png" title="download dataset">';
			html += '<span class="minitool" id="dstitle"><b>'+title+'...</b></span >';
			html += '<div style="display:none;" class="dataset_details"></div>';
			html += '<div class="minitoolbar">';
			html += '<span class="minitool"><img width="24px" height="24px" id="downloadds" src="includes/images/019.png"></span>';
			html += '<span class="minitool"><img width="24px" height="24px"  src="includes/images/022.png"></span>';
			html += '<span class="minitool"><img width="24px" height="24px" id="zoomds" src="includes/images/040.png"></span>';
			html += '<span class="minitool"><img id="morearrow" width="24px" height="24px" src="includes/images/092.png"></span>';
			html += '</div>'
			
			$("#dsdata")
			.append('<tr class="dsrow"><td>'+html+'</td></tr>');
			
			switch (features[i].attributes.udi.substring(0,2))
			{
				case "R1":
					R1++;
					break;
				case "R2":
					R2++;
					break;
				case "Y1":
					Y1++;
					break;
			}
		};
		
		$('.dsdetails #downloadds').click()
		.click(function() {
			var udi=$(this).closest('.dsdetails').attr('id');
			window.location = 'https://data.gulfresearchinitiative.org/data-discovery?filter=' + udi;
		});
		
		$('.dsdetails #detailsds').click()
		.click(function() {
			//dostuff
		});
		
		$('.dsdetails #zoomds').click(function() {
			console.debug($(this).closest('.dsdetails').attr('id'));
			if ($(this).attr("src") == "includes/images/041.png")
			{
				$(this).attr("src","includes/images/040.png");
				gotoAllFeatures();
			}
			else
			{
				$(".dsdetails #zoomds").attr("src","includes/images/040.png");
				//gotoAllFeatures();
				$(this).attr("src","includes/images/041.png");
				gotoFeature($(this).closest('.dsdetails').attr('id'));
			}
		});
		
		$(".dsdetails")
		.hover(function(){highlightFeature(this.id)},function(){unhighlightFeature(this.id)});
		
		$("#dstitle, .dsdetails #morearrow").click(function() {
			loadDetails($(this).closest('.dsdetails').attr('id'));
		});
		
		loadStats();
		
		//gotoAllFeatures();
	}
	
	function gotoAllFeatures()
	{
		map.zoomToExtent(vector.getDataExtent());
	}
	
	function gotoFeature(udi)
	{
		var myFeature=vector.getFeaturesByAttribute("udi",udi)[0];
		map.zoomToExtent(myFeature.geometry.getBounds())
	}
	
	function highlightFeature(udi)
	{
		var myFeature=vector.getFeaturesByAttribute("udi",udi)[0];
		selectControl.select(myFeature);
	}
	
	function unhighlightFeature(udi)
	{
		var myFeature=vector.getFeaturesByAttribute("udi",udi)[0];
		selectControl.unselect(myFeature);
	}
	
	function highlightRow(udi)
	{
		$('div[id="'+udi+'"]').addClass("datasethl");
	}
	
	function unhighlightRow(udi)
	{
		$('div[id="'+udi+'"]').removeClass("datasethl");
	}
	
	//OpenLayers.ProxyHost = "proxy.cgi?url=";
	
	function init()
	{
		
		var renderer = OpenLayers.Util.getParameters(window.location.href).renderer;
		renderer = (renderer) ? [renderer] : OpenLayers.Layer.Vector.prototype.renderers;
		
		map = new OpenLayers.Map( 
		{
			div: "olmap",
			projection: 'EPSG:4326',
			displayProjection: 'EPSG:900913',
			minResolution: "auto",
			maxResolution: "auto",
			numZoomLevels: 12,
			buffer: 2,
			//zoomDuration: 100,
			controls: [
				new OpenLayers.Control.Navigation(),
				new OpenLayers.Control.Zoom(),
				new OpenLayers.Control.KeyboardDefaults()
			],
			eventListeners: {
				featureover: function(e) 
				{
					e.feature.renderIntent = "select";
					e.feature.layer.drawFeature(e.feature);
					var uid=e.feature.attributes.udi;
					highlightRow(uid);
				},
				featureout: function(e) 
				{
					e.feature.renderIntent = "default";
					e.feature.layer.drawFeature(e.feature);
					var uid=e.feature.attributes.udi;
					unhighlightRow(uid);
				},
				featureclick: function(e) 
				{
					var udi = e.feature.attributes.udi;
					console.log("Map says: " + e.feature.id + " clicked on " + e.feature.layer.name + ' UDI=' + udi);
				}
			}
		});
		
		var myStyles = new OpenLayers.StyleMap(
		{
			"default": new OpenLayers.Style(
			{
				strokeColor: "cyan",
				strokeOpacity: 0.75,
				fillColor: "black",
				strokeWidth: 3,
				fillOpacity: 0,
				graphicZIndex: 1
			},
			{
				rules: [
                new OpenLayers.Rule({
                    // a rule contains an optional filter
                    filter: new OpenLayers.Filter.Comparison({
                        type: OpenLayers.Filter.Comparison.LIKE,
                        property: "udi", // the "foo" feature attribute
                        value: "R1"
                    }),
					// if a feature matches the above filter, use this symbolizer
                    symbolizer: {
                        strokeColor: "#FF9700",
						fillColor: "#FF9700"
                    }
                }),
				new OpenLayers.Rule({
                    // a rule contains an optional filter
                    filter: new OpenLayers.Filter.Comparison({
                        type: OpenLayers.Filter.Comparison.LIKE,
                        property: "udi", // the "foo" feature attribute
                        value: "R2"
                    }),
					// if a feature matches the above filter, use this symbolizer
                    symbolizer: {
                        strokeColor: "#00AB6F",
						fillColor: "#00AB6F"
                    }
                }),
				new OpenLayers.Rule({
                    // a rule contains an optional filter
                    filter: new OpenLayers.Filter.Comparison({
                        type: OpenLayers.Filter.Comparison.LIKE,
                        property: "udi", // the "foo" feature attribute
                        value: "Y"
                    }),
					// if a feature matches the above filter, use this symbolizer
                    symbolizer: {
                        strokeColor: "#2219B2",
						fillColor: "#2219B2"
                    }
                })
				
				]
				
			}),
			"select": new OpenLayers.Style(
			{
				//strokeColor: "white",
				strokeOpacity: 1,
				fillOpacity: .3,
				strokeWidth: 5,
				label: "${udi}",
				fontColor: "white",
				labelOutlineColor: "black",
				labelOutlineWidth: 3,
				graphicZIndex: 2
			})
		});
		
		google_hybrid = new OpenLayers.Layer.Google('Google', 
		{
			type: google.maps.MapTypeId.HYBRID,
			//numZoomLevels: 20,
			sphericalMercator: true
		});

		vector = new OpenLayers.Layer.Vector("Datasets", {
			projection: 'EPSG:3857',
			//strategies: [new OpenLayers.Strategy.BBOX({resFactor: 1})],
			strategies: [new OpenLayers.Strategy.Fixed()],
			protocol: new OpenLayers.Protocol.WFS({
				//url: "http://gomaportal.fw.tamucc.edu/arcgis/services/Test/GRIIDCdata/MapServer/WFSServer?",
				url: "/WFSServer",
				featurePrefix:"Test_GRIIDCdata",
				featureType: "GRIIDCdata_3857",
				version: "1.1.0",
				geometryName: "Shape",
				extractAttributes: true,
				srsName: "EPSG:3857"
			}),
			styleMap: myStyles,
			renderers: renderer,
			rendererOptions: {zIndexing: true},
			transitionEffect: 'resize'
		});
		
		vector.events.register('loadend', vector, function () {
			console.log('Done loading vector layer');
			showList(vector.features);
		});
		
		map.addLayers([google_hybrid, vector]);
		
		var overview1 = new OpenLayers.Control.OverviewMap({
            maximized: false,
            maximizeTitle: 'Show the overview map',
            minimizeTitle: 'Hide the overview map',
			size: new OpenLayers.Size(400,200),
			maxResolution: 22,
			numZoomLevels: 5
        });
        map.addControl(overview1);
		
		map.setCenter(new OpenLayers.LonLat(lon, lat).transform('EPSG:4326', 'EPSG:3857'), zoom);
		
		selectControl = new OpenLayers.Control.SelectFeature(vector);
		map.addControls([selectControl]);
		selectControl.activate();
		
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
		
		drawings = new OpenLayers.Layer.Vector("Filter", {
			styleMap: filterStyles,
			rendererOptions: {zIndexing: true}
		});
			
		map.addLayer(drawings);
		draw = new OpenLayers.Control.DrawFeature(drawings, OpenLayers.Handler.Polygon);
		map.addControl(draw);
		//draw.activate();
		
		drawings.events.on({
			beforefeatureadded: function(event) {
				var geo = event.feature.geometry;
				vector.filter = new OpenLayers.Filter.Spatial({
					type: OpenLayers.Filter.Spatial.INTERSECTS,
					value: geo
				});
				vector.refresh({force: true});
				//return false;
				drawings.removeAllFeatures();
				$("#dnav").click();
			}
		});
	}
	
	function loadStats()
	{
		var html = '<table>';
		html += "<tr><td>Showing <b>" + vector.features.length + "</b> Records</td></tr>";
		html += "<tr><td>RFP-I: <b>" + R1 + "</b>";
		html += ", RFP-II: <b>" + R2 + "</b>";
		html += ", Year One: <b>" + Y1 + "</b>";
		html +=  "</td><tr></table>";
		
		$("#dsstats").html(html);
	}
	
	function showLikeUDI(udi)
	{
		if (drawings.features.length > 0)
		{
			vector.filter = new OpenLayers.Filter.Logical({
					type: OpenLayers.Filter.Logical.AND,
					filters: [
						new OpenLayers.Filter.Comparison({
							type: OpenLayers.Filter.Comparison.LIKE,
							property: "udi",
							value: udi
						}),
						new OpenLayers.Filter.Spatial({
							type: OpenLayers.Filter.Spatial.INTERSECTS,
							value: drawings.features[0].geometry
						})
					]
				});
		}
		else
		{
			vector.filter = new OpenLayers.Filter.Comparison({
				type: OpenLayers.Filter.Comparison.LIKE,
				property: "udi",
				value: udi
			});
		}
		vector.refresh({force: true});
		vector.redraw();
	}
	
	function initTB()
	{
		$("#showall").button()
		.click(function() {
			showLikeUDI("*");
		});
		
		$("#showr1").button()
		.click(function() {
			showLikeUDI("R1*");
		});
		
		$("#showr2").button()
		.click(function() {
			showLikeUDI("R2*");
		});
		
		$("#showy1").button()
		.click(function() {
			showLikeUDI("Y*");
		});
		
		$("#ndraw").button()
		.click(function() {
			draw.activate();
			$("#ndraw").toggle();
			$("#dnav").toggle();
		});
		
		$("#dnav").button()
		.click(function() {
			draw.deactivate();
			$("#dnav").toggle();
			$("#ndraw").toggle();
		});
		
		$("#filtclr").button()
		.click(function() {
			drawings.removeAllFeatures();
			showLikeUDI("*");
		});
		
		
		$("#zoomout").button()
		.click(function() {
			map.zoomToMaxExtent();
		});
		
		$("#zoomhome").button()
		.click(function() {
			//map.zoomToExtent(vector.getDataExtent());
			map.setCenter(new OpenLayers.LonLat(lon, lat).transform('EPSG:4326', 'EPSG:3857'), zoom);
		});
	
	}
	
	function loadQtip()
	{
		$.fn.qtip.defaults = $.extend(true, {}, $.fn.qtip.defaults, {
            position: {
                adjust: {
                    method: "flip flip",
                    mouse: false
                },
                viewport: $(window)
            },
            show: {
                event: "mouseenter focus",
                solo: true
            },
            hide: {
                event: "mouseleave blur",
                delay: 100,
                fixed: true
            },
            style: {
                classes: "ui-tooltip-shadow ui-tooltip-tipped",
                tip: {
                    corner: true,
                    offset: 10
                },
                'font-size': 12
            }
        });
		
		$("#ndraw").qtip({
            content: {
                text: "Draw a polygon on the map."
            },
            position: {
                my: "bottom left",
                at: "top right",
                viewport: $(window)
            }
        });
		
		$("#dnav").qtip({
            content: {
                text: "Switch to navigate mode."
            },
            position: {
                my: "bottom left",
                at: "top right",
                viewport: $(window)
            }
        });
		
		$("#filtclr").qtip({
            content: {
                text: "Clear all filters."
            },
            position: {
                my: "bottom left",
                at: "top right",
                viewport: $(window)
            }
        });
		
		
		$("#zoomhome").qtip({
            content: {
                text: "Zoom to show all visible features."
            },
            position: {
                my: "bottom left",
                at: "top right",
                viewport: $(window)
            }
        });
		
		$("#showr2").qtip({
            content: {
                text: "Show only RFP-II data."
            },
            position: {
                my: "bottom left",
                at: "top right",
                viewport: $(window)
            }
        });
		
		$("#showr1").qtip({
            content: {
                text: "Show only RFP-I data."
            },
            position: {
                my: "bottom left",
                at: "top right",
                viewport: $(window)
            }
        });
		
		$("#showy1").qtip({
            content: {
                text: "Show only Year One data."
            },
            position: {
                my: "bottom left",
                at: "top right",
                viewport: $(window)
            }
        });
		
		$("#showall").qtip({
            content: {
                text: "Show everything."
            },
            position: {
                my: "bottom left",
                at: "top right",
                viewport: $(window)
            }
        });
		
		
		
		
	}
</script>

<body>

<table width="100%" height="100%" border="0">
	<tr>
		<td colspan="2" width="100%">
		<div class="mptoolbar">
			<span width="" class="twolines">
			Spatial Filter
			</span>
			<span class="mptool"><img id="ndraw" src="includes/images/083.png"></span>
			<span class="mptool"><img id="dnav" style="display:none;" src="includes/images/012.png"></span>
			<span class="mptool"><img id="filtclr" src="includes/images/031.png"></span>
			<!--<span class="mptool"><img id="zoomout" src="includes/images/i_zoomfull.png"></span>-->
			<span class="mptool"><img id="zoomhome" src="includes/images/040a.png"></span>
			<img class="placeholder" src="includes/images/bar.png">
			<span class="twolines">Funding Cycle:</span>
			<span class="mptool"><img id="showy1" src="includes/images/year_one.png"></span>
			<span class="mptool"><img id="showr1" src="includes/images/rfp_i.png"></span>
			<span class="mptool"><img id="showr2" src="includes/images/rfp_ii.png"></span>
			<span class="mptool"><img id="showall" src="includes/images/002.png"></span>
			<img class="placeholder" src="includes/images/bar.png">
			<span class="twolines">Text Filter:</span> 
			<input id="filtertxt" size="60" type="text">
			<img class="placeholder" src="includes/images/bar.png">
			<span class="dsstats" id="dsstats">
				Loading...
			</span>
		</div>
		</td>
	</tr>
	<tr valign="top">
		<td width="70%">
			<div id="olmap" class="grmap"></div>
		</td>
		<td width="30%" valign="top">
		<div class="datasets">
			<table width="100%">
				<tr>
					<td valign="top" width="100%">
						<div>
							<!--this is the table that contains all the datasets rows-->
							<table width="100%" >
								<tbody id="dsdata">
									<!-- Placeholder for datasets -->
								</tbody>
							</table>
						</div>
					</td>
				</tr>
			</table>
			</div>
		</td>
		
	</tr>
</table>


