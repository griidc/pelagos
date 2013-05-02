<?php
#include 'CI_Contact_DL.php';
include_once 'CI_Telephone.php';
include_once 'CI_Address.php';

class CI_Contact
{
	public function __construct($instanceType, $instanceName, $onlineresource)
	{
		$instanceType .= '-gmd:CI_Contact';
		
		echo '<fieldset>';
		echo '<legend>Contact_'.$instanceName.'</legend>';
		
		${'mytelephone'.$instanceName} = new CI_Telephone($instanceType.'-gmd:phone',$instanceName);
		${'myaddress'.$instanceName} = new CI_Address($instanceType.'-gmd:address', $instanceName, $onlineresource);
		
		echo ${'myaddress'.$instanceName}->getHTML();
		
		//echo '<label for="gmd:hoursOfService_'.$instanceName.'">hoursOfService</label>';
		//echo '<input type="text" name="gmd:hoursOfService_'.$instanceName.'" xmlclass="gmd:hoursOfService" xmltype="gco:CharacterString"/><br/>';
		
		//echo '<label for="gmd:contactInstructions_'.$instanceName.'">contactInstructions</label>';
		//echo '<input type="text" name="gmd:contactInstructions_'.$instanceName.'" xmlclass="gmd:contactInstructions" xmltype="gco:CharacterString"/><br/>';
		
		echo '</fieldset>';
	}
	
}


	/*		
				
	<fieldset>
	<legend>Contact</legend>
		
	
		<label for="gmd:hoursOfService">hoursOfService</label>
		<input type="text" name="gmd:hoursOfService" xmltype="gco:CharacterString"/><br/>
		
		<label for="gmd:contactInstructions">contactInstructions</label>
		<input type="text" name="gmd:contactInstructions" xmltype="gco:CharacterString"/><br/>

	
	</fieldset>
	*/
?>
	