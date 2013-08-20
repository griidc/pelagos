<?php
include_once 'CI_Contact.php';
include_once 'CI_RoleCode.php';

class CI_ResponsibleParty
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType, $instanceName, $online=false, $role='', $Legend='Responsible Party', $poc='Individual Name')
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
		
		$mycontact = new CI_Contact($mMD, $instanceType.'-gmd:contactInfo', $instanceName, $contactArray, $online);
		$Contact = $mycontact->getHTML();
		//$myrolecode = new CI_RoleCode($instanceName.'-ROLE');
		
		//$mMD->validateRules .= $mMD->twig->render('js/CI_ResponsibleParty_Rules.js', array('instanceName' => $instanceName));
		
		$twigArr = array('instanceName' => $instanceName,'instanceType' => $instanceType,'role' => $role,'Contact' => $Contact,'poc' => $poc, 'Legend' => $Legend,'xmlArray' => $xmlArray[0]);
		
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