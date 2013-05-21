<?php
include 'EX_GeographicBoundingBox.php';
include 'EX_TemporalExtent.php';


class EX_Extent
{
	public function __construct($mMD, $instanceType,$instanceName)
	{
		$xmlArray = $mMD->returnPath($instanceType);
					
		$instanceType .= "-gmd:EX_Extent!$instanceName";
		
		$geoArray = $xmlArray[0]["gmd:geographicElement"];
		$tempArray = $xmlArray[0]["gmd:temporalElement"];

		$myggbb = new EX_GeographicBoundingBox($mMD, $instanceType.'-gmd:geographicElement',$instanceName, $geoArray);
		$mytmpext = new EX_TemporalExtent($mMD, $instanceType.'-gmd:temporalElement',$instanceName, $tempArray);
		
		$GeographicBoundingBox = $myggbb->getHTML();
		
		$TemporalExtent = $mytmpext->getHTML();
		
		$twigArr = array('instanceName' => $instanceName, 'instanceType' => $instanceType,'GeographicBoundingBox' => $GeographicBoundingBox,'TemporalExtent' => $TemporalExtent, 'xmlArray' => $xmlArray[0]);
		
		$this->htmlString = $mMD->twig->render('html/EX_Extent.html', $twigArr);

		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
	
}



?>