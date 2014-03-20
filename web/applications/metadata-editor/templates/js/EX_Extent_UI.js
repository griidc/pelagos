	
	$("#EXbtn_{{instanceName}}").button().click(function()
	{
		$("#geoWizard").dialog({
			title: 'Geometry Wizard',
			autoOpen: false,
			modal: true
		});
		$("#geoWizard").load("includes/mapWizard.html",{"instanceName":"{{instanceName}}"});
	
	});