<?php
// @codingStandardsIgnoreFile
include_once 'CI_ResponsibleParty.php';
include_once 'MD_DataIdentifcation.php';
include_once 'MD_Distribution.php';

class MI_Metadata
{
	private $htmlString;
	
	public function __construct($mMD, $instanceName,$fileIdentifier)
	{
		
		$instanceType = "gmi:MI_Metadata";
		
		$xmlArray = $mMD->returnPath("gmi:MI_Metadata/gmd:fileIdentifier");

		if (isset($xmlArray[0]))
		{
			$fileIdentifier = $xmlArray[0];
		}
        
        date_default_timezone_set('UTC');
        
		$now = date('c');
		
		$mypi = new CI_ResponsibleParty($mMD,$instanceType.'-gmd:contact','contactPI',false,'pointOfContact','Metadata Contact','The name of the individual responsible for maintaining the metadata, typically the scientists or researcher who created the dataset.');
		$mydi = new MD_DataIdentification($mMD,$instanceType.'-gmd:identificationInfo','DataIdent');
		$mydisinfo = new MD_Distribution($mMD,$instanceType.'-gmd:distributionInfo','DistroInfo');
		
		$ResponsibleParty = $mypi->getHTML();
		$DataIdentification = $mydi->getHTML();
		$DistributionInfo = $mydisinfo->getHTML();
		
		$twigArr = array('instanceType' => $instanceType,'instanceName' => $instanceName,'fileIdentifier' => $fileIdentifier,'now' => $now,'ResponsibleParty' => $ResponsibleParty,'DataIdentification' => $DataIdentification, 'DistributionInfo' => $DistributionInfo);
		
		$this->htmlString .= $mMD->twig->render('html/MI_Metadata.html', $twigArr);
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
}
?>

