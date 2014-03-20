
	smGeoViz = new GeoViz();

	smGeoViz.initMap('BPLmap_{{instanceName}}',{'onlyOneFeature':false,'allowModify':false,'allowDelete':false,'staticMap':true});
	
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
	
	 $( "#dtabs-4" ).show(function() {
		 console.log('shown');
		 smGeoViz.flashMap();
		 smGeoViz.gotoAllFeatures();
		 $('#BPL1_{{instanceName}}').change(function() {
			console.log('it Changed!');
			smGeoViz.removeAllFeaturesFromMap();
			smGeoViz.gmlToWKT($('#BPL1_{{instanceName}}').val());
		 });
	 });
	 