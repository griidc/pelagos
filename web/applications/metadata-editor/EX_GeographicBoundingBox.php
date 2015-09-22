<?php
// @codingStandardsIgnoreFile

class EX_GeographicBoundingBox
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType, $instanceName, $xmlArray)
	{
		$xmlArray = $mMD->returnPath($instanceType);
		
		$instanceType .= '-gmd:EX_GeographicBoundingBox';
		
		$twigArr = array('instanceName' => $instanceName, 'instanceType' => $instanceType, 'xmlArray' => $xmlArray[0]);
		
		$this->htmlString = $mMD->twig->render('html/EX_GeographicBoundingBox.html', $twigArr);
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
}








?>