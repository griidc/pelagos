<?php
include 'EX_GeographicBoundingBox.php';
include 'EX_TemporalExtent.php';


class EX_Extent
{
	public function __construct($mMD, $instanceType,$instanceName)
	{
		$instanceType .= "-gmd:EX_Extent!$instanceName";

		$myggbb = new EX_GeographicBoundingBox($mMD, $instanceType.'-gmd:geographicElement',$instanceName);
		$mytmpext = new EX_TemporalExtent($mMD, $instanceType.'-gmd:temporalElement',$instanceName);
		
		$GeographicBoundingBox = $myggbb->getHTML();
		
		$TemporalExtent = $mytmpext->getHTML();
		
		$twigArr = array('instanceName' => $instanceName, 'instanceType' => $instanceType,'GeographicBoundingBox' => $GeographicBoundingBox,'TemporalExtent' => $TemporalExtent);
		
		$this->htmlString = $mMD->twig->render('html/EX_Extent.html', $twigArr);

		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
	
}



?>