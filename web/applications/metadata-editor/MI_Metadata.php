<?php
include_once 'CI_ResponsibleParty.php';
include_once 'MD_DataIdentifcation.php';
include_once 'MD_Distribution.php';

class MI_Metadata
{
	private $htmlString;
	
	public function __construct($mMD, $instanceName,$fileIdentifier)
	{
		
		$instanceType = "/gmi:MI_Metadata";
				
		$now = substr(date('c'),0,10);
		
		$mypi = new CI_ResponsibleParty($mMD,$instanceType.'-gmd:contact','contactPI',false,'CI_RoleCode_principalInvestigator','Principal Investigator');
		$mydi = new MD_DataIdentification($mMD,$instanceType.'-gmd:identificationInfo','DataIdent');
		$mydisinfo = new MD_Distribution($mMD,$instanceType.'-gmd:distributionInfo','DistroInfo');
		
		$ResponsibleParty = $mypi->getHTML();
		$DataIdentification = $mydi->getHTML();
		$DistributionInfo = $mydisinfo->getHTML();
		
		$twigArr = array('instanceName' => $instanceName,'fileIdentifier' => $fileIdentifier,'now' => $now,'ResponsibleParty' => $ResponsibleParty,'DataIdentification' => $DataIdentification, 'DistributionInfo' => $DistributionInfo);
		
		$this->htmlString .= $mMD->twig->render('html/MI_Metadata.html', $twigArr);
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
}
?>

