<?php
// @codingStandardsIgnoreFile

class CI_OnlineResource
{
	private $htmlString;
	
	public function __construct($mMD, $instanceName, $instanceType, $forContact = false)
	{
		$instanceType .= "-gmd:CI_OnlineResource";
				
		$xmlArray = $mMD->returnPath($instanceType);
		
		$twigArr = array('instanceName' => $instanceName,'instanceType' => $instanceType, 'forContact' => $forContact, 'xmlArray' => $xmlArray[0]);
		
		$this->htmlString .= $mMD->twig->render('html/CI_OnlineResource.html', $twigArr);
		
		$mMD->jsString .= $mMD->twig->render('js/CI_OnlineResource.js', array('instanceName' => $instanceName));
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
}
?>	