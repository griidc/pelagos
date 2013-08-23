<?php

class EX_BoundingPolygon
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType, $instanceName)
	{
		$xmlArray = $mMD->returnPath($instanceType);
		
		$instanceType .= '-gmd:EX_BoundingPolygon';
		
		$twigArr = array('instanceName' => $instanceName, 'instanceType' => $instanceType, 'xmlArray' => $xmlArray[0]);
		
		$this->htmlString = $mMD->twig->render('html/EX_BoundingPolygon.html', $twigArr);
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
}

?>
<!--
<gmd:EX_BoundingPolygon id="boundingPolygon"> 
	<gmd:polygon> 
		<gml:Polygon> 
		<gml:interior> 
			<gml:LinearRing> 
				<gml:coordinates decimal=" 156.86274,71.34815 -156.87389,71.33893 -156.88004,71.33883 -156.89144,71.33259 -156.89982,71.33182 156.86274,71.34815 "/> 
			</gml:LinearRing> 
		</gml:interior> 
	</gml:Polygon> 
	</gmd:polygon> 
</gmd:EX_BoundingPolygon>
-->