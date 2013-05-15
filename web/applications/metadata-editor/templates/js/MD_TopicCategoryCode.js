
function addTopicKWItem{{instanceName}}()
{
	var tpkList=document.getElementById("TOPKlist_{{instanceName}}");
	var tpkSel=document.getElementById("TOPKselect_{{instanceName}}");
	//var tpkTxt=document.getElementById("TOPK_{{instanceName}}");
		
	var option=document.createElement("option");
	option.text=tpkList.options[tpkList.selectedIndex].text;
	tpkSel.add(option,null);
	
	tpkList.remove(tpkList.selectedIndex);
	
	makeTopiclist{{instanceName}}();
}

function removeTopicKWItem{{instanceName}}()
{
	var tpkList=document.getElementById("TOPKlist_{{instanceName}}");
	var tpkSel=document.getElementById("TOPKselect_{{instanceName}}");
	//var tpkTxt=document.getElementById("TOPK_{{instanceName}}");
	
	var option=document.createElement("option");
	option.text=tpkSel.options[tpkSel.selectedIndex].text;
	tpkList.add(option,null);
	
	tpkSel.remove(tpkSel.selectedIndex);
	
	makeTopiclist{{instanceName}}();
}

function makeTopiclist{{instanceName}}()
{
	var tpkSel=document.getElementById("TOPKselect_{{instanceName}}");
	var tpkTxt=document.getElementById("TOPK_{{instanceName}}");
	var txt="";
	var i;
	for (i=0;i<tpkSel.length;i++)
	{
		if (txt=="")
		{
			txt=txt + tpkSel.options[i].text;
		}
		else
		{
			txt=txt + ";" + tpkSel.options[i].text;
		}
	}
	tpkTxt.value = txt;
}
