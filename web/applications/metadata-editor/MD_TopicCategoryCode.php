<?php

class MD_TopicCategoryCode
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType, $instanceName)
	{
		//$instanceType .= '-gmd:MD_TopicCategoryCode';
		
		$twigArr = array('instanceName' => $instanceName, 'instanceType' => $instanceType);
		
		$this->htmlString = $mMD->twig->render('html/MD_TopicCategoryCode.html', $twigArr);
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
}