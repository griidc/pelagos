<title>GeoViz Mapping Tool</title>
<link type="text/css" rel="stylesheet" href="/sites/all/modules/jquery_update/replace/ui/themes/base/minified/jquery.ui.theme.min.css" />
<link type="text/css" rel="stylesheet" href="/includes/qTip2/jquery.qtip.min.css" />
<script type="text/javascript" src="//code.jquery.com/jquery-1.8.2.js"></script>
<script type="text/javascript" src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<script type="text/javascript" src="/includes/openlayers/lib/OpenLayers.js"></script>
<script type="text/javascript" src="//maps.google.com/maps/api/js?v=3&sensor=false"></script>
<script type="text/javascript" src="/includes/qTip2/jquery.qtip.min.js"></script>


<!--<script src="/includes/geoviz/geoviz.js"></script>-->
<script src="/~mvandeneijnden/map/geoviz.js"></script>

<script>
	$(document).ready(function() 
	{
		$.fn.qtip.defaults = $.extend(true, {}, $.fn.qtip.defaults, {
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
                classes: "ui-tooltip-shadow ui-tooltip-tipped"
            },
			position: {
                adjust: {
                    method: "flip flip"
                },
                my: "top left",
                at: "bottom right",
                viewport: $(window)
			}
        });
		
		initMap('olmap',{'onlyOneFeature':true,'allowModify':true,'allowDelete':true});
		initToolbar('maptoolbar',{'showDrawTools':false,'showExit':true});
	});
	
	$(document).on('featureAdded', function(e, eventInfo) { 
		$('#BPL1_DataIdent',window.opener.document).val(eventInfo);
		console.log(eventInfo);
	});
	
	$(document).on('imready', function(e) {
		if ($('#BPL1_DataIdent',window.opener.document).val() != "")
		{
			addFeatureFromcoordinateList($('#BPL1_DataIdent',window.opener.document).val());
		}
	});
	
	
</script>

<table width="100%" height="100%" border="1">
	<tr>
		<td colspan="2" height="50px" width="100%">
		<div id="maptoolbar" style="background: #ffffff url('/sites/all/themes/griidc/images/green/body-bg.png') 0 0 repeat-x; padding: 10px;"></div>
		</td>
	</tr>
	<tr valign="top">
		<td width="70%">
			<!--Make sure the width and height of the map are 100%-->
			<div id="olmap" style="width: 100%;height: 100%;"></div>
		</td>
	</tr>
</table>