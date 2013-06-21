<?php
include 'EX_GeographicBoundingBox.php';
include 'EX_TemporalExtent.php';


class EX_Extent
{
	public function __construct($mMD, $instanceType,$instanceName)
	{
		$xmlArray = $mMD->returnPath($instanceType);
					
		$instanceType .= "-gmd:EX_Extent!$instanceName";
		
		$geoArray  = false;
		$tempArray = false;
		
		if (is_array($xmlArray))
		{
			if (array_key_exists("gmd:geographicElement",$xmlArray[0]))
			{
				$geoArray = $xmlArray[0]["gmd:geographicElement"];
			}
			
			if (array_key_exists("gmd:temporalElement",$xmlArray[0]))
			{
				$tempArray = $xmlArray[0]["gmd:temporalElement"];
			}
		}

		$myggbb = new EX_GeographicBoundingBox($mMD, $instanceType.'-gmd:geographicElement',$instanceName, $geoArray);
		$mytmpext = new EX_TemporalExtent($mMD, $instanceType.'-gmd:temporalElement',$instanceName, $tempArray);
		
		$GeographicBoundingBox = $myggbb->getHTML();
		
		$TemporalExtent = $mytmpext->getHTML();
		
		$twigArr = array('instanceName' => $instanceName, 'instanceType' => $instanceType,'GeographicBoundingBox' => $GeographicBoundingBox,'TemporalExtent' => $TemporalExtent, 'xmlArray' => $xmlArray[0]);
		
		$this->htmlString = $mMD->twig->render('html/EX_Extent.html', $twigArr);
		
		$mMD->jsString .= $mMD->twig->render('js/EX_Extent.js', array('instanceName' => $instanceName));

		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
	
}



?>