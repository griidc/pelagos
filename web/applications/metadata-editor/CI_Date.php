<?php
// @codingStandardsIgnoreFile
#include 'CI_Date_DL.php';


class CI_Date
{
	private $htmlString;
	
	private $dateTypes =
	array (
		"" => "[Please Select Date Type]",
		"creation" => "Creation",
		"publication" => "Publication",
		"revision" => "Revision"
	);
	
	public function __construct($mMD, $instanceType,$instanceName,$xmlArray, $dateType)
	{
		$instanceType .= "-gmd:CI_Date!$instanceName".'newdate';
		
		$twigArr = array('instanceName' => $instanceName, 'instanceType' => $instanceType,'dateType' => $dateType, 'xmlArray' => $xmlArray["gmd:CI_Date"], 'dateTypes' => $this->dateTypes);

		$this->htmlString .= $mMD->twig->render('html/CI_Date.html', $twigArr);
		
		$mMD->jqUIs .= $mMD->twig->render('js/CI_Date_UI.js', array('instanceName' => $instanceName));
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
}
?>	