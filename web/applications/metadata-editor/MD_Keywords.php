<?php

class MD_Keywords
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType, $instanceName,$type, $Legend='Keywords')
	{
		$instanceType .= '-gmd:MD_Keywords';
		
		$this->htmlString .= $mMD->twig->render('html/MD_Keywords.html', array('instanceName' => $instanceName, 'instanceType' => $instanceType, 'type' => $type, 'Legend' => $Legend));
		
		$mMD->jsString .= $mMD->twig->render('js/MD_Keywords.js', array('instanceName' => $instanceName));
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
}

?>