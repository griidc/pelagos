<?php
// @codingStandardsIgnoreFile

include_once 'MD_Distributor.php';

class MD_Distribution
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType, $instanceName,$Legend='Data Identification')
	{
		//$xmlArray = $mMD->returnPath($instanceType);
		
		$instanceType .= "-gmd:MD_Distribution!$instanceName";
		
		$mydistro = new MD_Distributor($mMD, $instanceType.'-gmd:distributor', $instanceName);
		$this->htmlString = $mydistro->getHTML();
		
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
	
}

?>