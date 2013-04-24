<?php
#include 'CI_Address_DL.php';
include_once 'CI_OnlineResource.php';



class CI_Address
{
	
	public function __construct($instanceType, $instanceName, $onlineresource)
	{
		$instanceType .= '-gmd:CI_Address';
		
		echo '<fieldset>';
		echo '<legend>Address_'.$instanceName.'</legend>';
		
		echo <<<CIAD
		
		<label for="CIAD1_$instanceName">deliveryPoint</label>
		<input type="text" id="CIAD1_$instanceName" name="$instanceType-gmd:deliveryPoint-gco:CharacterString"/><br/>
		
		<label for="CIAD2_$instanceName">city</label>
		<input type="text" id="CIAD2_$instanceName" name="$instanceType-gmd:city-gco:CharacterString"/><br/>
		
		<label for="CIAD3_$instanceName">administrativeArea</label>
		<input type="text" id="CIAD3_$instanceName" name="$instanceType-gmd:administrativeArea-gco:CharacterString"/><br/>
		
		<label for="CIAD4_$instanceName">postalCode</label>
		<input type="text" id="CIAD4_$instanceName" name="$instanceType-gmd:postalCode-gco:CharacterString"/><br/>
		
		<label for="CIAD5_$instanceName">country</label>
		<input type="text" id="CIAD5_$instanceName" name="$instanceType-gmd:country-gco:CharacterString"/><br/>
		
		<label for="CIAD6_$instanceName">electronicMailAddress</label>
		<input type="text" id="CIAD6_$instanceName" name="$instanceType-gmd:electronicMailAddress-gco:CharacterString"/><br/>
CIAD;
		
		if ($onlineresource==true)
		{
			$myonlr = new CI_OnlineResource($instanceName);
		}
		
		echo '</fieldset>';
	}
	
}



		/*
				
	<fieldset>
	<legend>Address</legend>
	
		<label for="gmd:deliveryPoint">deliveryPoint</label>
		<input type="text" name="gmd:deliveryPoint" xmltype="gco:CharacterString"/><br/>
		
		<label for="gmd:city">city</label>
		<input type="text" name="gmd:city" xmltype="gco:CharacterString"/><br/>
		
		<label for="gmd:administrativeArea">administrativeArea</label>
		<input type="text" name="gmd:administrativeArea" xmltype="gco:CharacterString"/><br/>
		
		<label for="gmd:postalCode">postalCode</label>
		<input type="text" name="gmd:postalCode" xmltype="gco:CharacterString"/><br/>
		
		<label for="gmd:country">country</label>
		<input type="text" name="gmd:country" xmltype="gco:CharacterString"/><br/>
		
		<label for="gmd:electronicMailAddress">electronicMailAddress</label>
		<input type="text" name="gmd:electronicMailAddress" xmltype="gco:CharacterString"/><br/>

	
	</fieldset>
	
	*/
?>	