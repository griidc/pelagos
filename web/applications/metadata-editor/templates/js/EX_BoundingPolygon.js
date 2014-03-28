
	smGeoViz = new GeoViz();

	smGeoViz.initMap('BPLmap_{{instanceName}}',{'onlyOneFeature':false,'allowModify':false,'allowDelete':false,'staticMap':true});
	
	$("#BPLbtn_{{instanceName}}").button().click(function()
	{
		$("#geoWizard").load("includes/mapWizard.html",{"instanceName":"{{instanceName}}"});
	
	});
	
	if ($('#BPL1_{{instanceName}}').val() != "")
	{
		smGeoViz.gmlToWKT($('#BPL1_{{instanceName}}').val());
	}
		
	$(document).on('imready', function(e,who) {
			if (who == '#BPLmap_{{instanceName}}')
			{
				console.log('Small Map Ready');
				setTimeout( function() { 
					smGeoViz.updateMap();
						}, 300);
				}
			}
		);
			
	$('#BPLmap_{{instanceName}}').on('gmlConverted', function(e, eventObj) {
		smGeoViz.removeAllFeaturesFromMap();
		var addedFeature = smGeoViz.addFeatureFromWKT(eventObj);
		smGeoViz.gotoAllFeatures();
	});
	
	$("#dtabs-4").on('active', function() {
		console.log('tabs4-click');
		smGeoViz.flashMap();
		smGeoViz.gotoAllFeatures();
		setTimeout( function() { 
		if ($('#BPL1_{{instanceName}}').val() == "")
		{
			smGeoViz.addImage('includes/images/notdefined.png',1);
		}}, 150);
		
		$('#BPL1_{{instanceName}}').change(function() {
			console.log('it Changed!');
			smGeoViz.goHome();
			smGeoViz.removeImage()
			smGeoViz.removeAllFeaturesFromMap();
			smGeoViz.gmlToWKT($('#BPL1_{{instanceName}}').val());
			if ($('#BPL1_{{instanceName}}').val() == "")
			{
				smGeoViz.addImage('includes/images/notdefined.png',1);
			}
		});
	});
	 