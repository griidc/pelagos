<?php

class CI_Telephone
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType, $instanceName)
	{
		$instanceType .= '-gmd:CI_Telephone';
		
		$twigArr = array('instanceName' => $instanceName,'instanceType' => $instanceType);
		
		$this->htmlString .= $mMD->twig->render('html/CI_Telephone.html', $twigArr);
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
}
?>			