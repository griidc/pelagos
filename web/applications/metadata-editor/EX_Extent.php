<?php
include 'EX_GeographicBoundingBox.php';
include 'EX_TemporalExtent.php';


class EX_Extent
{
	public function __construct($instanceType,$instanceName)
	{
		$instanceType .= "-gmd:EX_Extent!$instanceName";
		
		echo '<fieldset>';
		echo '<legend>Extent_'.$instanceName.'</legend>';
		
		echo '<label for="EX1_'.$instanceName.'">description</label>';
		echo '<input type="text" id="EX1_'.$instanceName.'" name="'.$instanceType.'-gmd:description-gco:CharacterString"/><br/>';

		$myggbb = new EX_GeographicBoundingBox($instanceType.'-gmd:geographicElement',$instanceName);
		$mytmpext = new EX_TemporalExtent($instanceType.'-gmd:temporalElement',$instanceName);
		
		
		echo '</fieldset>';
		
	}
	
	
}



?>