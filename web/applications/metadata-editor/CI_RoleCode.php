<?php
// @codingStandardsIgnoreFile
#include 'CI_RoleCode_DL.php';


class CI_RoleCode
{
	private $htmlString;
	
	private $RoleCodes =
	array (
		"" => "[Please Select a Role]",
		"resourceProvider" => "Resource Provider",
		"custodian" => "Custodian",
		"owner" => "Owner",
		"distributor" => "Distributor",
		"originator" => "Originator",
		"pointOfContact" => "Point of Contact",
		"principalInvestigator" => "Principal Investigator",
		"processor" => "Processor",
		"publisher" => "Publisher",
		"author" => "Author"
	);
	
	public function __construct($mMD, $instanceType, $instanceName, $selrole = "", $disabledCtrl)
	{
		$xmlArray = $mMD->returnPath($instanceType);
		
		$twigArr = array('instanceName' => $instanceName, 'instanceType' => $instanceType,'RoleCodes' => $this-> RoleCodes, 'selrole' => $selrole, 'disabledCtrl' => $disabledCtrl);
		
		$this->htmlString .= $mMD->twig->render('html/CI_RoleCode.html', $twigArr);
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
	
}

?>	