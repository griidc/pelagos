<?php
include_once 'CI_Citation.php';
include_once 'CI_ResponsibleParty.php';
include_once 'MD_Keywords.php';
include_once 'MD_TopicCategoryCode.php';

class MD_DataIdentification
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType, $instanceName,$Legend='Data Identification')
	{
		$xmlArray = $mMD->returnPath($instanceType);
		
		$instanceType .= "-gmd:MD_DataIdentification!$instanceName";
			
		${'myci'.$instanceName} = new CI_Citation($mMD, $instanceType.'-gmd:citation', $instanceName);
		$Citation = ${'myci'.$instanceName}->getHTML();
		
		$mydataic = new CI_ResponsibleParty($mMD,$instanceType.'-gmd:pointOfContact',$instanceName,false,'CI_RoleCode_pointOfContact','Dataset Contact','The name of the individual responsible for the creation of the dataset or majority of it in cases wherein a dataset is a compilation of data files.');
		
		$ResponsibleParty = $mydataic->getHTML();
			
		${'mykwtheme'.$instanceName} = new MD_Keywords($mMD, $instanceType.'-gmd:descriptiveKeywords!theme', $instanceName.'Theme','theme','Theme Keywords');
		${'mykwplace'.$instanceName} = new MD_Keywords($mMD, $instanceType.'-gmd:descriptiveKeywords!place', $instanceName.'Place','place','Place Keywords');
		
		$ThemeKeywords = ${'mykwtheme'.$instanceName}->getHTML();
		$PlaceKeywords = ${'mykwplace'.$instanceName}->getHTML();
		
		#Topic Keywords MD_TopicCategoryCode
		${'mytopickw'.$instanceName} = new MD_TopicCategoryCode($mMD, $instanceType.'-gmd:topicCategory', $instanceName);
		$TopicCategory =  ${'mytopickw'.$instanceName}->getHTML();
		
		include_once 'EX_Extent.php';
		$myext = new EX_Extent($mMD, $instanceType.'-gmd:extent',$instanceName);
		
		$Extent = $myext->getHTML();
		
		$twigArr = array('instanceName' => $instanceName,'instanceType' => $instanceType,'Citation' => $Citation, 'ResponsibleParty' => $ResponsibleParty,'ThemeKeywords' => $ThemeKeywords,'PlaceKeywords' => $PlaceKeywords,'TopicCategory' => $TopicCategory,'Extent' => $Extent, 'Legend' => $Legend, 'xmlArray' => $xmlArray[0]);
		
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