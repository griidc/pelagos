
var lastdata{{instanceName}} = [];
var forced = false;

jQuery(function() {
    if (document.getElementById("CRIP2_{{instanceName}}").value == 'GRIIDC')
    {
        forced = true;
        jQuery('#CRPF_{{instanceName}}').click();
        forced = false;
    }
});


function addHiddenElement_{{instanceName}}(elementName,elementValue)
{
	var element = document.getElementById(elementName);

	element.disabled = true;
	lastdata{{instanceName}}.push(element.value);
	element.value = elementValue;

    var newInput = document.createElement("TEXTAREA");

	newInput.id = element.id+'hidden';
	newInput.name = element.name;
	newInput.value = element.value;
    newInput.style.display = "none";
	element.parentNode.insertBefore(newInput, element);
    $(element).removeClass('error');
}

function removeHiddenElement_{{instanceName}}(elementName,elementValue)
{
	var element = document.getElementById(elementName);
	var child = document.getElementById(elementName+'hidden');

	element.disabled = false;
	element.value = elementValue;
	element.parentNode.removeChild(child);

}

function prefill_{{instanceName}}(what)
{
	if (what.checked)
	{
		addHiddenElement_{{instanceName}}("CRIP2_{{instanceName}}","GRIIDC");
		addHiddenElement_{{instanceName}}("CITP1_{{instanceName}}","+1-361-825-3604");
		addHiddenElement_{{instanceName}}("CITP2_{{instanceName}}","+1-361-825-2050");
		addHiddenElement_{{instanceName}}("CIAD1_{{instanceName}}","6300 Ocean Drive\r\nUnit 5869");
		addHiddenElement_{{instanceName}}("CIAD2_{{instanceName}}","Corpus Christi");
		addHiddenElement_{{instanceName}}("CIAD3_{{instanceName}}","Texas");
		addHiddenElement_{{instanceName}}("CIAD4_{{instanceName}}","78412-5869");
		addHiddenElement_{{instanceName}}("CIAD5_{{instanceName}}","USA");
		addHiddenElement_{{instanceName}}("CIAD6_{{instanceName}}","help@griidc.org");
		addHiddenElement_{{instanceName}}("OLR1_{{instanceName}}","http://data.griidc.org");

		document.getElementById("lastdata_{{instanceName}}").value = lastdata{{instanceName}};
	}
	else
	{
		lastdata{{instanceName}} = document.getElementById("lastdata_{{instanceName}}").value.split(",");

		removeHiddenElement_{{instanceName}}("CRIP2_{{instanceName}}",lastdata{{instanceName}}[0]);
		removeHiddenElement_{{instanceName}}("CITP1_{{instanceName}}",lastdata{{instanceName}}[1]);
		removeHiddenElement_{{instanceName}}("CITP2_{{instanceName}}",lastdata{{instanceName}}[2]);
		removeHiddenElement_{{instanceName}}("CIAD1_{{instanceName}}",lastdata{{instanceName}}[3]);
		removeHiddenElement_{{instanceName}}("CIAD2_{{instanceName}}",lastdata{{instanceName}}[4]);
		removeHiddenElement_{{instanceName}}("CIAD3_{{instanceName}}",lastdata{{instanceName}}[5]);
		removeHiddenElement_{{instanceName}}("CIAD4_{{instanceName}}",lastdata{{instanceName}}[6]);
		removeHiddenElement_{{instanceName}}("CIAD5_{{instanceName}}",lastdata{{instanceName}}[7]);
		removeHiddenElement_{{instanceName}}("CIAD6_{{instanceName}}",lastdata{{instanceName}}[8]);
		removeHiddenElement_{{instanceName}}("OLR1_{{instanceName}}",lastdata{{instanceName}}[9]);

		document.getElementById("lastdata_{{instanceName}}").value = "";

		lastdata{{instanceName}} = [];

	}

    if (!forced) {
        if (onceValidated) {
            $('#metadata').valid();
            validateTabs(false);
        }
    }
}
