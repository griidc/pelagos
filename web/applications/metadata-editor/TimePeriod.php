<?php
class TimePeriod
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType,$instanceName)
	{
		$mMD->jqUIs .= $mMD->twig->render('js/TimePeriod_UI.js', array('instanceName' => $instanceName));
		
		$twigArr = array('instanceName' => $instanceName, 'instanceType' => $instanceType);
		$this->htmlString = $mMD->twig->render('html/TimePeriod.html', $twigArr);
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
	
}
?>