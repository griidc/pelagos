
function addTopicKWItem{{instanceName}}()
{
	var tpkList=document.getElementById("TOPKlist_{{instanceName}}");
	var tpkSel=document.getElementById("TOPKselect_{{instanceName}}");
	//var tpkTxt=document.getElementById("TOPK_{{instanceName}}");
		
	var option=document.createElement("option");
	option.text=tpkList.options[tpkList.selectedIndex].text;
	option.value=tpkList.options[tpkList.selectedIndex].value;
	tpkSel.add(option,null);
	
	tpkList.remove(tpkList.selectedIndex);
	
	tpkSel.selectedIndex = tpkSel.length-1;
	
	makeTopiclist{{instanceName}}();
	sortSelect(tpkList);
	sortSelect(tpkSel);
	
}

function removeTopicKWItem{{instanceName}}()
{
	var tpkList=document.getElementById("TOPKlist_{{instanceName}}");
	var tpkSel=document.getElementById("TOPKselect_{{instanceName}}");
	//var tpkTxt=document.getElementById("TOPK_{{instanceName}}");
	
	var option=document.createElement("option");
	option.text=tpkSel.options[tpkSel.selectedIndex].text;
	option.value=tpkSel.options[tpkSel.selectedIndex].value;
	tpkList.add(option,null);
	
	tpkSel.remove(tpkSel.selectedIndex);
	
	tpkSel.selectedIndex = tpkSel.length-1;
	
	makeTopiclist{{instanceName}}();
	
	sortSelect(tpkList);
	sortSelect(tpkSel);
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
			txt=txt + tpkSel.options[i].value;
		}
		else
		{
			txt=txt + ";" + tpkSel.options[i].value;
		}
	}
	tpkTxt.value = txt;
}
