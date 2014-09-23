

function findSpot(options,insert)
{
	var sOrder = new Array();
	{% for key, value in topicOrder %}
	sOrder['{{key}}']={{value}};
	{% endfor %}
		
	var l = options.length,
    option = null;
	for (var i = 0; i < l; i++) {
		option = options[i];
		
		if (sOrder[option.value] > sOrder[insert])
		{
			return i;//sOrder[option.value];
		}
	}
	
	//return 0;
}

function addTopicKWItem{{instanceName}}()
{
	var tpkList=document.getElementById("TOPKlist_{{instanceName}}");
	var tpkSel=document.getElementById("TOPKselect_{{instanceName}}");
		
	var option=document.createElement("option");
	option = tpkList.options[tpkList.selectedIndex];
       
    option.innerHTML = tpkList.options[tpkList.selectedIndex].innerHTML; // Needed for IE
    option.value = tpkList.options[tpkList.selectedIndex].value; // Needed for IE
	
	rs = findSpot(tpkSel.options,option.value);
		
	tpkSel.add(option,tpkSel.options[rs]);
	
    try {
        tpkList.remove(tpkList.selectedIndex);
    } catch (e) {
        console.log(e.message)
    }   
	
	tpkSel.selectedIndex = tpkSel.length-1;
	
	makeTopiclist{{instanceName}}();
	//sortSelect(tpkList);
	//sortSelect(tpkSel);
	
}

function removeTopicKWItem{{instanceName}}()
{
	var tpkList=document.getElementById("TOPKlist_{{instanceName}}");
	var tpkSel=document.getElementById("TOPKselect_{{instanceName}}");
	
	var option=document.createElement("option");
	option = tpkSel.options[tpkSel.selectedIndex];
	
	rs = findSpot(tpkList.options,option.value);
	
	tpkList.add(option,tpkList.options[rs]);
	
    try {
        tpkSel.remove(tpkSel.selectedIndex);
    } catch (e) {
        console.log(e.message)
    }
	
	tpkSel.selectedIndex = tpkSel.length-1;
	
	makeTopiclist{{instanceName}}();
	
	//sortSelect(tpkList);
	//sortSelect(tpkSel);
}

function makeTopiclist{{instanceName}}()
{
    //debugger;
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
