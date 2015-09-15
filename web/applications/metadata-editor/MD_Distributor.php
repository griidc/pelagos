<?php
// @codingStandardsIgnoreFile

include_once 'MD_Format.php';
include_once 'MD_DigitalTransferOptions.php';

class MD_Distributor
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType, $instanceName,$Legend='Data Identification')
	{
		$xmlArray = $mMD->returnPath($instanceType);
		
		$instanceType .= "-gmd:MD_Distributor!$instanceName";
		
		$mydistrp = new CI_ResponsibleParty($mMD,$instanceType.'-gmd:distributorContact','contactDist',true,'distributor','Distribution Contact','Individual Name',true);
				
		$ResponsibleParty = $mydistrp->getHTML();
		
		$distArray = false;
		
		if (is_array($xmlArray))
		{
			if (array_key_exists("gmd:distributorTransferOptions",$xmlArray))
			{
				$distArray = $xmlArray[0]["gmd:distributorTransferOptions"];
			}
		}
		
		$transferOpt = new MD_DigitalTransferOptions($mMD, $instanceType.'-gmd:distributorTransferOptions', $instanceName, $distArray);
		$DigitalTransferOptions = $transferOpt->getHTML();
		
		$myFormat = new MD_Format($mMD, $instanceType.'-gmd:distributorFormat', $instanceName);
		$Format = $myFormat->getHTML();
		
		$twigArr = array('instanceName' => $instanceName,'instanceType' => $instanceType,'ResponsibleParty' => $ResponsibleParty,'DigitalTransferOptions' => $DigitalTransferOptions, 'Format' => $Format);
		
		$this->htmlString .= $mMD->twig->render('html/MD_Distributor.html', $twigArr);
		
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
	
}


?>