<?php
include_once 'CI_OnlineResource.php';

class CI_Address
{
	private $htmlString;

	public function __construct($mMD, $instanceType, $instanceName, $xmlArray, $onlineresource=false)
	{
		$myIni = $mMD->loadINI('CI_Address.ini');
		
		$instanceVars = $myIni["default"];
		
		$instanceType .= '-gmd:CI_Address';
		
		$twigArr = array('instanceName' => $instanceName, 'instanceType' => $instanceType, 'onlineresource' => $onlineresource, 'instanceVars' => $instanceVars ,'xmlArray' => $xmlArray["gmd:CI_Address"]);
		
		$this->htmlString .= $mMD->twig->render('html/CI_Address.html', $twigArr);
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}

}
?>	