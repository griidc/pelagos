<?php

class MD_Keywords
{
	
	public function __construct($instanceType, $instanceName,$type)
	{
		
		$instanceType .= '-gmd:MD_Keywords';
		
	echo <<<KW

	<script>
	function additem$instanceName()

	{
			
		var txtitem=document.getElementById("kwtxt$instanceName");
		
		if (txtitem.value == "")
		{
			return 0;
		}
		
		var lst=document.getElementById("kwlist$instanceName");
		var option=document.createElement("option");
		option.text=txtitem.value
		
		lst.add(option,null);
		lst.selectedIndex = lst.length-1;
		txtitem.value = "";
		
		makelist$instanceName();
		
	}

		function removeitem$instanceName()
		{
			var lst=document.getElementById("kwlist$instanceName");
			lst.remove(lst.selectedIndex);
			lst.selectedIndex = lst.length-1;
			makelist$instanceName();
		}
		
		function makelist$instanceName()
		{
			var x=document.getElementById("kwlist$instanceName");
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
			document.getElementById("kws$instanceName").value = txt;
		}


	</script>


	<fieldset>
	<legend>Keywords_$instanceName ($type)</legend>
	<table>
	<tr><td>
	<label for="kwtxt$instanceName">Keyword</label>
	<input type="text" id="kwtxt$instanceName">
	<td>
	<button type="button" size="10" onclick="javascript:additem$instanceName();">add</button>
	<br>
	<button type="button" size="10" onclick="javascript:removeitem$instanceName();">delete</button>
	</td>
	<td>
		<select size="5" id="kwlist$instanceName">
		</select>
	</td></tr>
	</table>
	<label for="kws$instanceName">keywords</label>
	<input type="text" id="kws$instanceName" name="$instanceType-gmd:keyword"/><br/>
	<label for="kwtype$instanceName">type</label>
	<input type="text" id="kwtype$instanceName" name="$instanceType-gmd:type-gmd:MD_KeywordTypeCode" value="$type"/><br/>
	</fieldset>

KW;

	}
	
}

?>