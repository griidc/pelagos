<?php
// @codingStandardsIgnoreFile

class CI_Telephone
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType, $instanceName, $xmlArray)
	{
		$myIni = $mMD->loadINI('CI_Telephone.ini');
		
		$instanceVars = $myIni["default"];
		
		$xmlArray = $mMD->returnPath($instanceType);
		
		$instanceType .= '-gmd:CI_Telephone';
		
		$twigArr = array('instanceName' => $instanceName,'instanceType' => $instanceType, 'instanceVars' => $instanceVars, 'xmlArray' => $xmlArray[0]);
		
		$this->htmlString .= $mMD->twig->render('html/CI_Telephone.html', $twigArr);
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
}
?>			