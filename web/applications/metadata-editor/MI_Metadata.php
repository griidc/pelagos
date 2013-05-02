<?php

include_once 'CI_ResponsibleParty.php';
include_once 'MD_DataIdentifcation.php';

class MI_Metadata
{
	private $htmlString;
	private $jsString;
	
	public function __construct($instanceName,$fileIdentifier)
	{
	
		$now = substr(date('c'),0,10);

		require_once '/usr/share/pear/Twig/Autoloader.php';
		Twig_Autoloader::register();
		
		$loader = new Twig_Loader_Filesystem('./templates');
		$twig = new Twig_Environment($loader);

		echo $twig->render('html/MI_Metadata_top.html', array('instanceName' => $instanceName,'fileIdentifier' => $fileIdentifier));

		$mypi = new CI_ResponsibleParty('gmd:contact','contactPI',false,'CI_RoleCode_principalInvestigator');
		//echo $mypi->getHTML();

		echo $twig->render('html/MI_Metadata_botm.html', array('instanceName' => $instanceName, 'now' => $now));
		
		$mydi = new MD_DataIdentification('gmd:identificationInfo','DataIdent');
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

