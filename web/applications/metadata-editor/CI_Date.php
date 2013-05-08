<?php
#include 'CI_Date_DL.php';


class CI_Date
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType,$instanceName,$dateType)
	{
		$instanceType .= "-gmd:CI_Date!$instanceName".'newdate';

		$this->htmlString .= $mMD->twig->render('html/CI_Date.html', array('instanceName' => $instanceName, 'instanceType' => $instanceType,'dateType' => $dateType));
		
		$mMD->jqUIs .= $mMD->twig->render('js/CI_Date_UI.js', array('instanceName' => $instanceName));
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
}
?>	