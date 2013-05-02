<?php
#include 'CI_Address_DL.php';
include_once 'CI_OnlineResource.php';



class CI_Address
{
	private $htmlString;
	private $jsString;

	public function __construct($instanceType, $instanceName, $onlineresource)
	{
		require_once '/usr/share/pear/Twig/Autoloader.php';
		Twig_Autoloader::register();
		
		$loader = new Twig_Loader_Filesystem('./templates');
		$twig = new Twig_Environment($loader);
		
		$instanceType .= '-gmd:CI_Address';
								
		$this->htmlString .= "<fieldset>\n";
		
		$this->htmlString .= "<legend>Address_$instanceName</legend>\n";
				
		$this->htmlString .= $twig->render('html/CI_Address.html', array('instanceName' => $instanceName, 'instanceType' => $instanceType));
		
		if ($onlineresource==true)
		{
			$myonlr = new CI_OnlineResource($instanceName);
		}
		
		$this->htmlString .= "</fieldset>\n";
		
		//echo $this->htmlstring;
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
?>	