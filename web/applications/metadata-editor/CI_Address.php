<?php
include_once 'CI_OnlineResource.php';

class CI_Address
{
	private $htmlString;

	public function __construct($mMD, $instanceType, $instanceName, $xmlArray, $onlineresource=false)
	{
		$instanceType .= '-gmd:CI_Address';
		
		$OnlineResource = '';
		
		if ($onlineresource==true)
		{
			$myonlr = new CI_OnlineResource($instanceName);
			//$OnlineResource = $myonlr->getHTML(); #TODO:Finish CI_OnlineResource Class
		}
		
		$twigArr = array('instanceName' => $instanceName, 'instanceType' => $instanceType, 'OnlineResource' => $OnlineResource, 'xmlArray' => $xmlArray["gmd:CI_Address"]);
		
		$this->htmlString .= $mMD->twig->render('html/CI_Address.html', $twigArr);
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}

}
?>	