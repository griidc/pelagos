
function popProtocol{{instanceName}}()
{
	var url=document.getElementById("OLR1_{{instanceName}}").value;
	var protocol=document.getElementById("OLR2_{{instanceName}}");
	var arr = url.split(":");
	
	protocol.value = arr[0];
}