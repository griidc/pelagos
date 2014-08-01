
function changeExtent{{instanceName}}(what)
{
	// document.getElementById("EX1_{{instanceName}}").disabled = !what;
	
	// document.getElementById("EX0_{{instanceName}}").checked = what;
	
	var checkStyle = "table-row";
    
    try
    {
        if (what)
        {
            document.getElementById("EX1_{{instanceName}}row").style.display = "table-row";
        }
        else
        {
            
                document.getElementById("EX1_{{instanceName}}row").style.display = "none";
        }
    }
    catch(err)
    {
        //Handle errors here
    }
	
	if (what)
	{
		checkStyle = "none";
	}
	else
	{
		checkStyle = "table-row";
	}
	
    try
    {
        document.getElementById("GDD2_{{instanceName}}row").style.display = checkStyle;
        document.getElementById("GDD3_{{instanceName}}row").style.display = checkStyle;
        document.getElementById("GDD4_{{instanceName}}row").style.display = checkStyle;
        document.getElementById("GDD5_{{instanceName}}row").style.display = checkStyle;
    }
    catch(err)
    {
        //Handle errors here
    }
    
    try
    {
        //document.getElementById("BPL1_{{instanceName}}row").style.display = checkStyle;
    }
    catch(err)
    {
        //Handle errors here
    }    
	
	document.getElementById("TM1_{{instanceName}}extentrow").style.display = checkStyle;
	document.getElementById("TM2_{{instanceName}}extentrow").style.display = checkStyle;
	document.getElementById("TM3_{{instanceName}}extentrow").style.display = checkStyle;
	
    try
    {
        document.getElementById("GDD1_{{instanceName}}").disabled = what;
        document.getElementById("GDD2_{{instanceName}}").disabled = what;
        document.getElementById("GDD3_{{instanceName}}").disabled = what;
        document.getElementById("GDD4_{{instanceName}}").disabled = what;
        document.getElementById("GDD5_{{instanceName}}").disabled = what;
    }
    catch(err)
    {
        //Handle errors here
    }
    
    try
    {
        document.getElementById("BPL1_{{instanceName}}").disabled = what;
    }
    catch(err)
    {
        //Handle errors here
    }
	
	document.getElementById("TM1_{{instanceName}}extent").disabled = what;
	document.getElementById("TM2_{{instanceName}}extent").disabled = what;
	document.getElementById("TM3_{{instanceName}}extent").disabled = what;

	//
	
	var validator = $("#metadata").validate();
	
	if (validator.numberOfInvalids() > 0)
	{
		//$("#metadata").valid();
	}
	
	if (!what)
	{
		$("#EX1_{{instanceName}}").removeClass("error");
	}
	else
	{
		$("#BPL1_{{instanceName}}").removeClass("error");
		$("#GDD2_{{instanceName}}").removeClass("error");
		$("#GDD3_{{instanceName}}").removeClass("error");
		$("#GDD4_{{instanceName}}").removeClass("error");
		$("#GDD5_{{instanceName}}").removeClass("error");
		
		$("#TM1_{{instanceName}}extent").removeClass("error");
		$("#TM2_{{instanceName}}extent").removeClass("error");
		$("#TM3_{{instanceName}}extent").removeClass("error");
	}
}