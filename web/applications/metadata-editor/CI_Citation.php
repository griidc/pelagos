<?php
#include 'CI_Telephone_DL.php';
include_once 'CI_Date.php';
include_once 'CI_ResponsibleParty.php';
include_once 'MD_Identifier.php';

class CI_Citation
{
	
	public function __construct($instanceType, $instanceName,$complex=false,$responsibleparty=false,$iscomplexcitation=false)
	{
		$instanceType .= '-gmd:CI_Citation';
		
		echo <<<CIT
		<fieldset>
		<legend>Citation_$instanceName</legend>
		
		<label for="CIT1_$instanceName">title</label>
		<input type="text" id="CIT1_$instanceName" name="$instanceType-gmd:title-gco:CharacterString"/><br/>

		<label for="CIT2_$instanceName"">alternateTitle</label>
		<input type="text" id="CIT2_$instanceName" name="$instanceType-gmd:alternateTitle-gco:CharacterString"/><br/>
CIT;
		
		$mycidate = new CI_Date($instanceType.'-gmd:date',$instanceName,'publication');
		
		if ($complex==true)
		{
		
			//echo '<label for="gmd:edition">edition</label>';
			//echo '<input type="text" name="gmd:edition" xmltype="gco:CharacterString"/><br/>';
			
			//echo '<label for="gmd:editionData">editionData</label>';
			//echo '<input type="text" name="gmd:editionData" xmltype="gco:CharacterString"/><br/>';
		}
		
		if ($iscomplexcitation==true)
		{
			$myidentifier = new MD_Identifier($instanceName);
		}
		
		if ($responsibleparty == true)
		{
			$myresp = new CI_ResponsibleParty($instanceName,true);
		}
		
		echo '</fieldset>';
	}
	
}

/*

	<fieldset>
	<legend>Citation</legend>
	
		<label for="gmd:title">title</label>
		<input type="text" name="gmd:deliveryPoint" xmltype="gco:CharacterString"/><br/>
		
		<label for="gmd:alternatetitle">alternatetitle</label>
		<input type="text" name="gmd:alternatetitle" xmltype="gco:CharacterString"/><br/>
		
		<?php
			$mycidate = new CI_Date('citation_date');
			
		
			$mypi = new CI_ResponsibleParty('DOI_Issuer',true);
		?>
		
		<label for="gmd:edition">edition</label>
		<input type="text" name="gmd:edition" xmltype="gco:CharacterString"/><br/>
		
		<label for="gmd:editionData">editionData</label>
		<input type="text" name="gmd:editionData" xmltype="gco:CharacterString"/><br/>
		
		
		
		
	
	</fieldset>
	
	*/

	?>