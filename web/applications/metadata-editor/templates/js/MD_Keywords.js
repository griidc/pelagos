
function additem{{instanceName}}()
{
	var txtitem=document.getElementById("kwtxt{{instanceName}}");
	
	if (txtitem.value == "")
	{
		return 0;
	}
	
	var lst=document.getElementById("kwlist{{instanceName}}");
	var option=document.createElement("option");
	option.text=txtitem.value
	
	lst.add(option,null);
	lst.selectedIndex = lst.length-1;
	txtitem.value = "";
	
	makelist{{instanceName}}();
}

function removeitem{{instanceName}}()
{
	var lst=document.getElementById("kwlist{{instanceName}}");
	lst.remove(lst.selectedIndex);
	lst.selectedIndex = lst.length-1;
	makelist{{instanceName}}();
}
	
function makelist{{instanceName}}()
{
	var x=document.getElementById("kwlist{{instanceName}}");
	var txt="";
	var i;
	for (i=0;i<x.length;i++)
	{
		if (txt=="")
		{
			txt=txt + x.options[i].text;
		}
		else
		{
			txt=txt + ";" + x.options[i].text;
		}
	}
	document.getElementById("kws{{instanceName}}").value = txt;
}
