
function changeExtent{{instanceName}}(what)
{
	document.getElementById("EX1_{{instanceName}}").disabled = !what.checked;
	
	document.getElementById("GDD1_{{instanceName}}").disabled = what.checked;
	document.getElementById("GDD2_{{instanceName}}").disabled = what.checked;
	document.getElementById("GDD3_{{instanceName}}").disabled = what.checked;
	document.getElementById("GDD4_{{instanceName}}").disabled = what.checked;
	document.getElementById("GDD5_{{instanceName}}").disabled = what.checked;
	
	document.getElementById("TM1_{{instanceName}}extent").disabled = what.checked;
	document.getElementById("TM2_{{instanceName}}extent").disabled = what.checked;
	document.getElementById("TM3_{{instanceName}}extent").disabled = what.checked;
}