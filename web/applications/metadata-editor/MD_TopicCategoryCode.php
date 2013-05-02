<?php

class MD_TopicCategoryCode
{
	private $htmlString;
	private $jsString;
	
	public function __construct($instanceType, $instanceName)
	{
		require_once '/usr/share/pear/Twig/Autoloader.php';
		Twig_Autoloader::register();
		
		$loader = new Twig_Loader_Filesystem('./templates');
		$twig = new Twig_Environment($loader);
		
		//$instanceType .= '-gmd:MD_TopicCategoryCode';
		
		$this->htmlString = $twig->render('html/MD_TopicCategoryCode.html', array('instanceName' => $instanceName, 'instanceType' => $instanceType));
		
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
	
	public function getJS()
	{
		return $this->jsString;
	}
}