
    geowizard = new MapWizard({"divSmallMap":"smlMDEMap","divSpatial":"spatial","divNonSpatial":"nonspatial","divSpatialWizard":"spatwizbtn","gmlField":"BPL1_{{instanceName}}","descField":"EX1_{{instanceName}}","spatialFunction":"changeExtent{{instanceName}}"});
	
	$("#dtabs-4").on('active', function() {
		geowizard.flashMap();
        $('#BPL1_{{instanceName}}').change();
        
        if ($('#BPL1_{{instanceName}}').val().length > 0 ) {geowizard.haveSpatial(false);}
        if ($('#EX1_{{instanceName}}').val().length > 0 ) {geowizard.haveSpatial(true);}
        
	});
    
    $('#BPL1_{{instanceName}}').change(function() {
        geowizard.haveGML($(this).val());
        geowizard.flashMap();
    });
	 