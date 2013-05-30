<?php

class CI_OnlineResource
{
	private $htmlString;
	
	public function __construct($mMD, $instanceName, $instanceType)
	{
		
		$instanceType .= "-gmd:CI_OnlineResource";
		
		//echo $instanceType . '<br>';
		
		//var_dump($mMD);
		
		$xmlArray = $mMD->returnPath($instanceType);
		
		$twigArr = array('instanceName' => $instanceName,'instanceType' => $instanceType,'xmlArray' => $xmlArray[0]);
	
		$this->htmlString .= $mMD->twig->render('html/CI_OnlineResource.html', $twigArr);
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
}
?>	