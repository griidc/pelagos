<?php
include 'TimePeriod.php';
include 'TimeInstant.php';


class EX_TemporalExtent
{
	public function __construct($instanceType, $instanceName,$alttype=false)
	{
		
		#$instanceType .= "-gmd:EX_TemporalExtent!$instanceName";
		$instanceType .= "-gmd:EX_TemporalExtent!$instanceName".'time';
		
		echo '<fieldset>';
		echo '<legend>TemporalExtent_'.$instanceName.'</legend>';

		if ($alttype==true)
		{
			$mytimep = new TimeInstant($instanceType, $instanceName.'timeperiod');
		}
		else
		{
			$mytimep = new TimePeriod($instanceType.'-gmd:extent-gml:TimePeriod', $instanceName.'extent');
		}
		
		echo '</fieldset>';
		
	}
	
	
}



?>