<?php
#include 'CI_Date_DL.php';


class CI_Date
{
	
	public function __construct($instanceType,$instanceName,$dateType)
	{
		$instanceType .= "-gmd:CI_Date!$instanceName".'newdate';

		echo <<<scrpt
		
		<script>
		(function ($) {
			$(function() {
				$( "#DATE1_$instanceName" ).datepicker({
					showOn: "button",
					buttonImageOnly: false,
					dateFormat: "yy-mm-dd",
					autoSize:true
				});
			});
		})(jQuery);
		</script>
scrpt;
		
		
		echo '<fieldset>';
		echo '<legend>Date_'.$instanceName.'</legend>';
		
		echo '<label for="DATE1_'.$instanceName.'">date</label>';
		echo '<input type="text" id="DATE1_'.$instanceName.'" name="'.$instanceType.'-gmd:date-gco:Date" value="2012-01-01"/><br/>';
		
		echo '<label for="DATE2_'.$instanceName.'">dateType</label>';
		echo '<input type="text" id="DATE2_'.$instanceName.'" name="'.$instanceType.'-gmd:dateType-gmd:CI_DateTypeCode" value="'.$dateType.'"/><br/>';
		
		echo '</fieldset>';
	}
	
}

/*
		
				
	<fieldset>
	<legend>Date</legend>
		
		<label for="gmd:date">date</label>
		<input type="text" name="gmd:date"/><br/>
		
		<label for="gmd:dateType">dateType</label>
		<input type="text" name="gmd:dateType"/><br/>

	
	</fieldset>
	*/
	?>	