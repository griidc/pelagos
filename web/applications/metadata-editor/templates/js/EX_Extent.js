
function changeExtent{{instanceName}}(what)
{
	document.getElementById("EX1_{{instanceName}}").disabled = !what.checked;
	
	var checkStyle = "table-row";
	
	
	if (what.checked)
	{
		document.getElementById("EX1_{{instanceName}}row").style.display = "table-row";
	}
	else
	{
		document.getElementById("EX1_{{instanceName}}row").style.display = "none";
	}
	
	if (what.checked)
	{
		checkStyle = "none";
	}
	else
	{
		checkStyle = "table-row";
	}
	
	document.getElementById("GDD2_{{instanceName}}row").style.display = checkStyle;
	document.getElementById("GDD3_{{instanceName}}row").style.display = checkStyle;
	document.getElementById("GDD4_{{instanceName}}row").style.display = checkStyle;
	document.getElementById("GDD5_{{instanceName}}row").style.display = checkStyle;
	
	document.getElementById("TM1_{{instanceName}}extentrow").style.display = checkStyle;
	document.getElementById("TM2_{{instanceName}}extentrow").style.display = checkStyle;
	document.getElementById("TM3_{{instanceName}}extentrow").style.display = checkStyle;
	
	document.getElementById("GDD1_{{instanceName}}").disabled = what.checked;
	document.getElementById("GDD2_{{instanceName}}").disabled = what.checked;
	document.getElementById("GDD3_{{instanceName}}").disabled = what.checked;
	document.getElementById("GDD4_{{instanceName}}").disabled = what.checked;
	document.getElementById("GDD5_{{instanceName}}").disabled = what.checked;
	
	document.getElementById("TM1_{{instanceName}}extent").disabled = what.checked;
	document.getElementById("TM2_{{instanceName}}extent").disabled = what.checked;
	document.getElementById("TM3_{{instanceName}}extent").disabled = what.checked;

	//
	
	var validator = $("#metadata").validate();
	
	if (validator.numberOfInvalids() > 0)
	{
		$("#metadata").valid();
	}
	
	if (!what.checked)
	{
		$("#EX1_{{instanceName}}").removeClass("error");
	}
	else
	{
		$("#GDD2_{{instanceName}}").removeClass("error");
		$("#GDD3_{{instanceName}}").removeClass("error");
		$("#GDD4_{{instanceName}}").removeClass("error");
		$("#GDD5_{{instanceName}}").removeClass("error");
		
		$("#TM1_{{instanceName}}extent").removeClass("error");
		$("#TM2_{{instanceName}}extent").removeClass("error");
		$("#TM3_{{instanceName}}extent").removeClass("error");
	}
}