<?php
// @codingStandardsIgnoreFile

class MD_Format
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType, $instanceName,$Legend='Format')
	{
		$xmlArray = $mMD->returnPath($instanceType);
		
		$instanceType .= "-gmd:MD_Format";
		
		$twigArr = array('instanceName' => $instanceName,'instanceType' => $instanceType,'Legend' => $Legend,'xmlArray' => $xmlArray[0]);
		
		$this->htmlString .= $mMD->twig->render('html/MD_Format.html', $twigArr);
		
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
	
}


?>