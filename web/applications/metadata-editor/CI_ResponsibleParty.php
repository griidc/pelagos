<?php
include_once 'CI_Contact.php';
include_once 'CI_RoleCode.php';

class CI_ResponsibleParty
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType, $instanceName, $onlineresource=false, $role='')
	{
		$instanceType .= "-gmd:CI_ResponsibleParty!$instanceName";
		
		$mycontact = new CI_Contact($mMD, $instanceType.'-gmd:contactInfo', $instanceName, $onlineresource);
		$Contact = $mycontact->getHTML();
		//$myrolecode = new CI_RoleCode($instanceName.'-ROLE');
		
		$mMD->validateRules .= $mMD->twig->render('js/CI_ResponsibleParty_Rules.js', array('instanceName' => $instanceName));
		
		$twigArr = array('instanceName' => $instanceName,'instanceType' => $instanceType,'role' => $role,'Contact' => $Contact);
		
		$this->htmlString .= $mMD->twig->render('html/CI_ResponsibleParty.html', $twigArr);
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
}
?>	