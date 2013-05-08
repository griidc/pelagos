<?php
class TimePeriod
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType,$instanceName)
	{
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