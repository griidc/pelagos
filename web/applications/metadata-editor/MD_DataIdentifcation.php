<?php
include_once 'CI_Citation.php';
include_once 'CI_ResponsibleParty.php';
include_once 'MD_Keywords.php';
include_once 'MD_TopicCategoryCode.php';

class MD_DataIdentification
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType, $instanceName)
	{
		$instanceType .= "-gmd:MD_DataIdentification!$instanceName";
			
		${'myci'.$instanceName} = new CI_Citation($mMD, $instanceType.'-gmd:citation', $instanceName);
		$Citation = ${'myci'.$instanceName}->getHTML();
		
		$mydataic = new CI_ResponsibleParty($mMD,$instanceType.'-gmd:pointOfContact',$instanceName,false,'CI_RoleCode_principalInvestigator');
		
		$ResponsibleParty = $mydataic->getHTML();
			
		${'mykwtheme'.$instanceName} = new MD_Keywords($mMD, $instanceType.'-gmd:descriptiveKeywords!theme', $instanceName.'Theme','theme');
		${'mykwplace'.$instanceName} = new MD_Keywords($mMD, $instanceType.'-gmd:descriptiveKeywords!place', $instanceName.'Place','place');
		
		$ThemeKeywords = ${'mykwtheme'.$instanceName}->getHTML();
		$PlaceKeywords = ${'mykwplace'.$instanceName}->getHTML();
		
		#Topic Keywords MD_TopicCategoryCode
		${'mytopickw'.$instanceName} = new MD_TopicCategoryCode($mMD, $instanceType.'-gmd:topicCategory', $instanceName);
		$TopicCategory =  ${'mytopickw'.$instanceName}->getHTML();
		
		include_once 'EX_Extent.php';
		$myext = new EX_Extent($mMD, $instanceType.'-gmd:extent',$instanceName);
		
		$Extent = $myext->getHTML();
		
		$twigArr = array('instanceName' => $instanceName,'instanceType' => $instanceType,'ResponsibleParty' => $ResponsibleParty,'ThemeKeywords' => $ThemeKeywords,'PlaceKeywords' => $PlaceKeywords,'TopicCategory' => $TopicCategory,'Extent' => $Extent);
		
		$this->htmlString .= $mMD->twig->render('html/MD_DataIdentification.html', $twigArr);
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}

}
?>