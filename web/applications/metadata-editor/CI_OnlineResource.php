<?php

class CI_OnlineResource
{
	private $htmlString;
	
	public function __construct($mMD, $instanceName, $instanceType, $Legend = "Online Resource")
	{
		
		$xmlArray = $mMD->returnPath($instanceType);
		
		$twigArr = array('instanceName' => $instanceName,'instanceType' => $instanceType, 'Legend' => $Legend,'xmlArray' => $xmlArray[0]);
	
		$this->htmlString .= $mMD->twig->render('html/CI_OnlineResource.html', $twigArr);
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
}
?>	