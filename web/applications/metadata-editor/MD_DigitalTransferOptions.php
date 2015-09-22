<?php
// @codingStandardsIgnoreFile

include_once 'CI_OnlineResource.php';

class MD_DigitalTransferOptions
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType, $instanceName, $xmlArray, $Legend="Transfer Options")
	{
		$xmlArray = $mMD->returnPath($instanceType);
	
		$instanceType .= '-gmd:MD_DigitalTransferOptions';
		
		
		
		$onlineResc = new CI_OnlineResource($mMD, $instanceName, $instanceType.'-gmd:onLine');
		$OnlineResource = $onlineResc->getHTML();
		
		$twigArr = array('instanceName' => $instanceName,'instanceType' => $instanceType, 'OnlineResource' => $OnlineResource, 'Legend' => $Legend, 'xmlArray' => $xmlArray[0]);
		$this->htmlString .= $mMD->twig->render('html/MD_DigitalTransferOptions.html', $twigArr);
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
}
?>			