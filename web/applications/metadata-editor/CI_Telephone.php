<?php
#include 'CI_Telephone_DL.php';


class CI_Telephone
{
	
	public function __construct($instanceType, $instanceName)
	{
		$instanceType .= '-gmd:CI_Telephone';
		
		echo '<fieldset>';
		echo '<legend>Telephone_'.$instanceName.'</legend>';
		
		echo '<label for="CITP1_'.$instanceName.'">voice</label>';
		echo '<input type="text" id="CITP1_'.$instanceName.'" name="'.$instanceType.'-gmd:voice-gco:CharacterString"/><br/>';
		
		echo '<label for="CITP2_'.$instanceName.'">facsimile</label>';
		echo '<input type="text" id="CITP2_'.$instanceName.'" name="'.$instanceType.'-gmd:facsimile-gco:CharacterString"/><br/>';
		
		echo '</fieldset>';
	}
	
}




/*
				
	<fieldset>
	<legend>Telephone</legend>
	
		<label for="gmd:voice">voice</label>
		<input type="text" name="gmd:voice" xmltype="gco:CharacterString"/><br/>

		<label for="gmd:facsimilee">facsimile</label>
		<input type="text" name="gmd:facsimile" xmltype="gco:CharacterString"/><br/>
	
	</fieldset>
*/
	?>			