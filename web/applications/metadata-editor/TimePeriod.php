<?php


class TimePeriod
{
	public function __construct($instanceType,$instanceName)
	{
		//$instanceType .= "-gmd:extent";
		
		echo '<fieldset>';
		echo '<legend>TimePeriod_'.$instanceName.'</legend>';
		
		echo '<label for="TM1_'.$instanceName.'">description</label>';
		echo '<input type="text" id="TM1_'.$instanceName.'" name="'.$instanceType.'-gml:description"/><br/>';
		
		echo '<label for="TM2_'.$instanceName.'">beginPosition</label>';
		echo '<input type="text" id="TM2_'.$instanceName.'" name="'.$instanceType.'-gml:beginPosition"/><br/>';
		
		echo '<label for="TM3_'.$instanceName.'">endPosition</label>';
		echo '<input type="text" id="TM3_'.$instanceName.'" name="'.$instanceType.'-gml:endPosition"/><br/>';
		
		//echo '<label for="TM4_'.$instanceName.'">duration</label>';
		//echo '<input type="text" id="TM4_'.$instanceName.'" name="'.$instanceType.'-gml:duration-gco:date"/><br/>';
		
		//echo '<label for="TM5_'.$instanceName.'">timeInterval</label>';
		//echo '<input type="text" id="TM5_'.$instanceName.'" name="'.$instanceType.'-gml:timeInterval-gco:float"/><br/>';
		
		echo '</fieldset>';
		
	}
	
	
}



?>