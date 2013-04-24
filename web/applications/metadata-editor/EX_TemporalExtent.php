<?php
include 'TimePeriod.php';
include 'TimeInstant.php';


class EX_TemporalExtent
{
	public function __construct($instanceType, $instanceName,$alttype=false)
	{
		
		echo '<fieldset>';
		echo '<legend>TemporalExtent_'.$instanceName.'</legend>';

		if ($alttype==true)
		{
			$mytimep = new TimeInstant($instanceType, $instanceName.'timeperiod');
		}
		else
		{
			$mytimep = new TimePeriod($instanceType.'-gml:TimePeriod', $instanceName.'extent');
		}
		
		echo '</fieldset>';
		
	}
	
	
}



?>