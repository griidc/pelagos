<title>GeoViz Template</title>
<link type="text/css" rel="stylesheet" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
<script type="text/javascript" src="//code.jquery.com/jquery-1.9.1.js"></script>
<script type="text/javascript" src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<script type="text/javascript" src="/includes/openlayers/lib/OpenLayers.js"></script>
<script type="text/javascript" src="//maps.google.com/maps/api/js?v=3&sensor=false"></script>

<script src="/includes/geoviz/geoviz.js"></script>

<script>
	$(document).ready(function() 
	{
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