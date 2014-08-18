
    geowizard = new MapWizard({"divSmallMap":"smlMDEMap","divSpatial":"spatial","divNonSpatial":"nonspatial","divSpatialWizard":"spatwizbtn","gmlField":"BPL1_{{instanceName}}","descField":"EX1_{{instanceName}}","spatialFunction":"changeExtent{{instanceName}}"});
	
	$("#dtabs-4").on('active', function() {
		console.log('tabs4-click');
		geowizard.flashMap();
        $('#BPL1_{{instanceName}}').change();
	});
    
    $('#BPL1_{{instanceName}}').change(function() {
        geowizard.haveGML($(this).val());
    });
	 