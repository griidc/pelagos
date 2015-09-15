<?php
// @codingStandardsIgnoreFile

class TimeInstant
{
	public function __construct($instanceType,$instanceName)
	{
		
		echo '<fieldset>';
		echo '<legend>TimeInstant_'.$instanceName.'</legend>';
		
		echo '<label for="TM_description_'.$instanceName.'">description</label>';
		echo '<input type="text" name="TM_description_'.$instanceName.'" xmlclass="TM_description" xmltype="gco:CharacterString"/><br/>';
		
		echo '<label for="TM_timePosition_'.$instanceName.'">timePosition</label>';
		echo '<input type="text" name="TM_timePosition_'.$instanceName.'" xmlclass="TM_timePosition" xmltype="gco:date"/><br/>';
		
		echo '</fieldset>';
		
	}
	
	
}
