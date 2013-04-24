<?php
#include 'CI_OnlineResource_DL.php';



class CI_OnlineResource
{
	
	public function __construct($instanceName)
	{
		echo '<fieldset>';
		echo '<legend>OnlineResource_'.$instanceName.'</legend>';
		
		echo '<label for="gmd:linkage_'.$instanceName.'">linkage</label>';
		echo '<input type="text" name="gmd:linkage_'.$instanceName.'" xmlclass="gmd:linkage" xmltype="gmd:URL"/><br/>';
		
		echo '<label for="gmd:protocol_'.$instanceName.'">protocol</label>';
		echo '<input type="text" name="gmd:protocol_'.$instanceName.'" xmlclass="gmd:protocol" xmltype="gco:CharacterString"/><br/>';
		
		echo '<label for="gmd:applicationProfile_'.$instanceName.'">applicationProfile</label>';
		echo '<input type="text" name="gmd:applicationProfile_'.$instanceName.'" xmlclass="gmd:applicationProfile" xmltype="gco:CharacterString"/><br/>';
		
		echo '<label for="gmd:name_'.$instanceName.'">name</label>';
		echo '<input type="text" name="gmd:name_'.$instanceName.'" xmlclass="gmd:name" xmltype="gco:CharacterString"/><br/>';
		
		echo '<label for="gmd:description_'.$instanceName.'">description</label>';
		echo '<input type="text" name="gmd:description_'.$instanceName.'" xmlclass="gmd:description" xmltype="gco:CharacterString"/><br/>';
		
		echo '<label for="gmd:function_'.$instanceName.'">role</label>';
		echo '<select name="gmd:function_'.$instanceName.'" xmlclass="gmd:role" xmltype="<gmd:CI_OnlineFunctionCode>"</select>';
		echo '<option>CI_OnLineFunctionCode_download</option>';
		echo '<option selected>CI_OnLineFunctionCode_information</option>';
		echo '<option>CI_OnLineFunctionCode_offlineAccess</option>';
		echo '<option>CI_OnLineFunctionCode_order</option>';
		echo '<option>CI_OnLineFunctionCode_search</option>';
		echo '</select>';
		
		
		
		echo '</fieldset>';
	}
	
}
?>	