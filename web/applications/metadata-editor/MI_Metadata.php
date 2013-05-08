<?php
include_once 'CI_ResponsibleParty.php';
include_once 'MD_DataIdentifcation.php';

class MI_Metadata
{
	private $htmlString;
	
	public function __construct($mMD, $instanceName,$fileIdentifier)
	{
		$now = substr(date('c'),0,10);
		
		$mypi = new CI_ResponsibleParty($mMD,'gmd:contact','contactPI',false,'CI_RoleCode_principalInvestigator');
		$mydi = new MD_DataIdentification($mMD,'gmd:identificationInfo','DataIdent');
		
		$ResponsibleParty = $mypi->getHTML();
		$DataIdentification = $mydi->getHTML();
		
		$twigArr = array('instanceName' => $instanceName,'fileIdentifier' => $fileIdentifier,'now' => $now,'ResponsibleParty' => $ResponsibleParty,'DataIdentification' => $DataIdentification);
		
		$this->htmlString .= $mMD->twig->render('html/MI_Metadata.html', $twigArr);
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
}
?>

