<?php
// @codingStandardsIgnoreFile
include_once 'CI_Citation.php';
include_once 'CI_ResponsibleParty.php';
include_once 'MD_Keywords.php';
include_once 'MD_TopicCategoryCode.php';
include_once 'EX_Extent.php';

class MD_DataIdentification
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType, $instanceName,$Legend='Dataset Information')
	{
		$xmlArray = $mMD->returnPath($instanceType);
		
		$instanceType .= "-gmd:MD_DataIdentification!$instanceName";
			
		${'myci'.$instanceName} = new CI_Citation($mMD, $instanceType.'-gmd:citation', $instanceName);
		$Citation = ${'myci'.$instanceName}->getHTML();
		
		$mydataic = new CI_ResponsibleParty($mMD,$instanceType.'-gmd:pointOfContact',$instanceName,false,'principalInvestigator','Dataset Contact','The name of the individual responsible for the creation of the dataset or majority of it in cases wherein a dataset is a compilation of data files.');
		
		$ResponsibleParty = $mydataic->getHTML();
			
		//${'mykwtheme'.$instanceName} = new MD_Keywords($mMD, $instanceType.'-gmd:descriptiveKeywords!theme', $instanceName.'Theme','theme');
		//${'mykwplace'.$instanceName} = new MD_Keywords($mMD, $instanceType.'-gmd:descriptiveKeywords!place', $instanceName.'Place','place');
		${'mykwtheme'.$instanceName} = new MD_Keywords($mMD, $instanceType, $instanceName.'Theme','theme',true);
		${'mykwplace'.$instanceName} = new MD_Keywords($mMD, $instanceType, $instanceName.'Place','place',false);
		
		$ThemeKeywords = ${'mykwtheme'.$instanceName}->getHTML();
		$PlaceKeywords = ${'mykwplace'.$instanceName}->getHTML();
		
		#Topic Keywords MD_TopicCategoryCode
		${'mytopickw'.$instanceName} = new MD_TopicCategoryCode($mMD, $instanceType.'-gmd:topicCategory', $instanceName);
		$TopicCategory =  ${'mytopickw'.$instanceName}->getHTML();
		
		$suplemental = null;
		if (is_array($xmlArray))
		{
			if (array_key_exists("gmd:supplementalInformation",$xmlArray[0]))
			{
				$suplemental = explode('|',$xmlArray[0]["gmd:supplementalInformation"]["gco:CharacterString"]);
			}
		}
				
		$myext = new EX_Extent($mMD, $instanceType.'-gmd:extent',$instanceName);
		
		$Extent = $myext->getHTML();
		
		$twigArr = array('instanceName' => $instanceName,'instanceType' => $instanceType,'Citation' => $Citation, 'ResponsibleParty' => $ResponsibleParty,'ThemeKeywords' => $ThemeKeywords,'PlaceKeywords' => $PlaceKeywords,'TopicCategory' => $TopicCategory,'Extent' => $Extent,'suplemental' => $suplemental, 'Legend' => $Legend, 'xmlArray' => $xmlArray[0]);
		
		$this->htmlString .= $mMD->twig->render('html/MD_DataIdentification.html', $twigArr);
		
		$mMD->jsString .= $mMD->twig->render('js/MD_DataIdentification.js', array('instanceName' => $instanceName));
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}

}
?>