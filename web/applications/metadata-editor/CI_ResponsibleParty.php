<?php
#include 'CI_ResponsibleParty_DL.php';
include_once 'CI_Contact.php';
include_once 'CI_RoleCode.php';

class CI_ResponsibleParty
{
	
	public function __construct($instanceType, $instanceName, $onlineresource=false, $role='')
	{
		$instanceType .= '-gmd:CI_ResponsibleParty';
		
		echo '<fieldset>';
		echo '<legend>Responsible Party_'.$instanceName.'</legend>';
		
		echo <<<CIRP
		
		<label for="CRIP1_$instanceName">individualName</label>
		<input type="text" id="CRIP1_$instanceName" name="$instanceType-gmd:individualName-gco:CharacterString"/><br/>
		
		<label for="CRIP2_$instanceName">organisationName</label>
		<input type="text" id="CRIP2_$instanceName" name="$instanceType-gmd:organisationName-gco:CharacterString"/><br/>
		
		<label for="CRIP3_$instanceName">positionName</label>
		<input type="text" id="CRIP3_$instanceName" name="$instanceType-gmd:positionName-gco:CharacterString"/><br/>
	

CIRP;
		
		$mycontact = new CI_Contact($instanceType.'-gmd:contactInfo', $instanceName, $onlineresource);
		//$myrolecode = new CI_RoleCode($instanceName.'-ROLE');
		
		echo '<label for="CRIP4_'.$instanceName.'">role</label>';
		echo '<input type="text" id="CRIP4_'.$instanceName.'" name="'.$instanceType.'-gmd:role-gmd:CI_RoleCode" value="'.$role.'"/><br/>';
		
		echo '</fieldset>';
	}
	
}





		/*
				
	<fieldset>
	<legend>Responsible Party</legend>
	
		<label for="gmd:individualName">individualName</label>
		<input type="text" name="gmd:individualName" xmltype="gco:CharacterString"/><br/>
		
		<label for="gmd:organisationName">organisationName</label>
		<input type="text" name="gmd:organisationName" xmltype="gco:CharacterString"/><br/>
		
		<label for="gmd:positionName">positionName</label>
		<input type="text" name="gmd:positionName" xmltype="gco:CharacterString"/><br/>
		
		<?php
			include 'CI_Contact.php';
		?>
		
		<?php
			include 'CI_RoleCode.php';
		?>
		

	
	</fieldset>
	*/
	?>	