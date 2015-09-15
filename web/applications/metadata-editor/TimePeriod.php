<?php
// @codingStandardsIgnoreFile
class TimePeriod
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType,$instanceName, $xmlArray)
	{
		$mMD->jqUIs .= $mMD->twig->render('js/TimePeriod_UI.js', array('instanceName' => $instanceName));
		
		//$mMD->validateRules .= $mMD->twig->render('js/Timeperiod_Rules.js', array('instanceType' => $instanceType));
		
		$twigArr = array('instanceName' => $instanceName, 'instanceType' => $instanceType, 'xmlArray' => $xmlArray["gml:TimePeriod"]);
		$this->htmlString = $mMD->twig->render('html/TimePeriod.html', $twigArr);
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
	
}
?>