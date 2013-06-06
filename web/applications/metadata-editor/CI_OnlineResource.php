<?php

class CI_OnlineResource
{
	private $htmlString;
	
	public function __construct($mMD, $instanceName, $instanceType)
	{
		$instanceType .= "-gmd:CI_OnlineResource";
				
		$xmlArray = $mMD->returnPath($instanceType);
		
		$twigArr = array('instanceName' => $instanceName,'instanceType' => $instanceType,'xmlArray' => $xmlArray[0]);
		
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