<?php

class MD_TopicCategoryCode
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType, $instanceName, $Legend='Keywords')
	{
		//$instanceType .= '-gmd:MD_TopicCategoryCode';
		
		$twigArr = array('instanceName' => $instanceName, 'instanceType' => $instanceType, 'Legend' => $Legend);
		
		$this->htmlString = $mMD->twig->render('html/MD_TopicCategoryCode.html', $twigArr);
		
		$mMD->jsString .= $mMD->twig->render('js/MD_TopicCategoryCode.js', array('instanceName' => $instanceName));
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
}