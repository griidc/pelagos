<?php

class EX_GeographicBoundingBox
{
	public function __construct($instanceType, $instanceName)
	{
		echo '<fieldset>';
		echo '<legend>GeographicBoundingBox_'.$instanceName.'</legend>';
		
		echo '<label for="GDD1_'.$instanceName.'">extentTypeCode</label>';
		echo '<input type="text" id="GDD1_'.$instanceName.'" name="'.$instanceType.'-gmd:extentTypeCode-gco:Boolean" value="1"/><br/>';
		
		echo '<label for="GDD2_'.$instanceName.'">westBoundLongitude</label>';
		echo '<input type="text" id="GDD2_'.$instanceName.'" name="'.$instanceType.'-gmd:westBoundLongitude-gco:Decimal"/><br/>';
		
		echo '<label for="GDD3_'.$instanceName.'">eastBoundLongitude</label>';
		echo '<input type="text" id="GDD3_'.$instanceName.'" name="'.$instanceType.'-gmd:eastBoundLongitude-gco:Decimal"/><br/>';
		
		echo '<label for="GDD4_'.$instanceName.'">southBoundLatitude</label>';
		echo '<input type="text" id="GDD4_'.$instanceName.'" name="'.$instanceType.'-gmd:southBoundLatitude-gco:Decimal"/><br/>';
		
		echo '<label for="GDD5_'.$instanceName.'">northBoundLatitude</label>';
		echo '<input type="text" id="GDD5_'.$instanceName.'" name="'.$instanceType.'-gmd:northBoundLatitude-gco:Decimal"/><br/>';
				
		
		echo '</fieldset>';
	}
}








?>