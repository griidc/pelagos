<?php
// @codingStandardsIgnoreFile
include_once 'CI_Contact.php';
include_once 'CI_RoleCode.php';

class CI_ResponsibleParty
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType, $instanceName, $online=false, $role='', $Legend='Responsible Party', $poc='Individual Name', $disabledCtrl = false)
	{
		
		$xmlArray = $mMD->returnPath($instanceType);
	
		$instanceType .= "-gmd:CI_ResponsibleParty";
	
		$contactArray = null;
		
		if (is_array($xmlArray))
		{
			if (array_key_exists("gmd:contactInfo",$xmlArray[0]))
			{
				$contactArray = $xmlArray[0]["gmd:contactInfo"]["gmd:CI_Contact"];
			}
		}
		
		$selrole = $xmlArray[0]["gmd:role"]["gmd:CI_RoleCode"]["@content"];
		
		if ($selrole == null)
		{
			$selrole = $role;
		}
		
		$mycontact = new CI_Contact($mMD, $instanceType.'-gmd:contactInfo', $instanceName, $contactArray, $online);
		$Contact = $mycontact->getHTML();
		//$myrolecode = new CI_RoleCode($instanceName.'-ROLE');
		
		//$mMD->validateRules .= $mMD->twig->render('js/CI_ResponsibleParty_Rules.js', array('instanceName' => $instanceName));
		
		$myrolecode = new CI_RoleCode($mMD, $instanceType.'-gmd:role', $instanceName, $selrole, $disabledCtrl);
		$Roles = $myrolecode->getHTML();
		
		$twigArr = array('instanceName' => $instanceName,'instanceType' => $instanceType,'Roles' => $Roles,'Contact' => $Contact, 'selrole' => $selrole, 'role' => $role,'poc' => $poc, 'Legend' => $Legend,'xmlArray' => $xmlArray[0]);
		
		if ($role == "distributor")
		{
			$mMD->jsString .= $mMD->twig->render('js/CI_ResponsibleParty.js', array('instanceName' => $instanceName));
		}
		
		$this->htmlString .= $mMD->twig->render('html/CI_ResponsibleParty.html', $twigArr);
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}

}
?>	