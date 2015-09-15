<?php
// @codingStandardsIgnoreFile
include_once 'CI_Citation.php';


class MD_Identifier
{
	
	public function __construct($instanceName)
	{
		
		echo '<fieldset>';
		echo '<legend>MD_Identifier_'.$instanceName.'</legend>';
	
		$mypi = new CI_Citation($instanceName.'_Identifier',true,true);
		
		echo '<label for="gmd:code_'.$instanceName.'">Code</label>';
		echo '<input type="text" name="gmd:code_'.$instanceName.'" xmlclass="gmd:code" xmltype="gco:CharacterString"/><br/>';
		
		echo '</fieldset>';
		
	}
}
?>