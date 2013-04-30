<?php
include_once 'CI_Citation.php';
include_once 'MD_Keywords.php';


class MD_DataIdentification
{
	
	public function __construct($instanceType, $instanceName)
	{
		$instanceType .= "-gmd:MD_DataIdentification!$instanceName";
		
		echo '<fieldset>';
		echo '<legend>Data_Identification_'.$instanceName.'</legend>';
			
		${'myci'.$instanceName} = new CI_Citation($instanceType.'-gmd:citation', $instanceName,'Authority');
		
		echo '<label for="MDD1_'.$instanceName.'">abstract</label>';
		echo '<input type="text" id="MDD1_'.$instanceName.'" name="'.$instanceType.'-gmd:abstract-gco:CharacterString"/><br/>';
		
		echo '<label for="MDD2_'.$instanceName.'">purpose</label>';
		echo '<input type="text" id="MDD2_'.$instanceName.'" name="'.$instanceType.'-gmd:purpose-gco:CharacterString"/><br/>';
		
		echo '<label for="MDD3_'.$instanceName.'">status</label>';
		echo '<input type="text" id="MDD3_'.$instanceName.'" name="'.$instanceType.'-gmd:status-gmd:MD_ProgressCode" value="completed"/><br/>';
		
		include_once 'CI_ResponsibleParty.php';
		$mydataic = new CI_ResponsibleParty($instanceType.'-gmd:pointOfContact',$instanceName,false,'CI_RoleCode_principalInvestigator');
			
		${'mykwtheme'.$instanceName} = new MD_Keywords($instanceType.'-gmd:descriptiveKeywords!theme', $instanceName.'Theme','theme');
		${'mykwplace'.$instanceName} = new MD_Keywords($instanceType.'-gmd:descriptiveKeywords!place', $instanceName.'Place','place');
		
		echo '<label for="MDD4_'.$instanceName.'">language</label>';
		echo '<input type="text" id="MDD2_'.$instanceName.'" name="'.$instanceType.'-gmd:language-gco:CharacterString"/><br/>';
		
		#Topic Keywords MD_TopicCategoryCode
		#${'mykwplace'.$instanceName} = new MD_Keywords($instanceType.'-gmd:topicCategory', $instanceName.'Place','place');
		
		include_once 'EX_Extent.php';
		$myext = new EX_Extent($instanceType.'-gmd:extent',$instanceName);
		
		echo '<label for="MDD4_'.$instanceName.'">supplementalInformation</label>';
		echo '<input type="text" id="MDD4_'.$instanceName.'" name="'.$instanceType.'-gmd:supplementalInformation-gco:CharacterString"/><br/>';
		
		
		
		
		echo '</fieldset>';

	}

}




?>