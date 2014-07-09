<?php

class EX_BoundingPolygon
{
    private $htmlString;
    
    public function __construct($mMD, $instanceType, $instanceName)
    {
        $xmlArray = $mMD->returnPath($instanceType);
             
        $geoArray = $xmlArray[0];
        
		$gmlCoordinates = '';
		
		$gmlCoordinates = $mMD->returnXmlString('http://www.isotc211.org/2005/gmd','polygon');
        
        // echo '<pre>';
		// var_dump($gmlCoordinates);
        // echo '</pre>';
              
        /* 
        if (isset ($geoArray['gmd:polygon']))
        {
            $gmlCoordinates = $geoArray['gmd:polygon'];
        }
		elseif (isset($geoArray['gmd:extentTypeCode']))
        {
            $polyCoordinates .= $geoArray['gmd:northBoundLatitude']['gco:Decimal'] . ',' . $geoArray['gmd:westBoundLongitude']['gco:Decimal'] . ' '; 
            $polyCoordinates .= $geoArray['gmd:northBoundLatitude']['gco:Decimal'] . ',' . $geoArray['gmd:eastBoundLongitude']['gco:Decimal'] . ' '; 
            $polyCoordinates .= $geoArray['gmd:southBoundLatitude']['gco:Decimal'] . ',' . $geoArray['gmd:eastBoundLongitude']['gco:Decimal'] . ' '; 
            $polyCoordinates .= $geoArray['gmd:southBoundLatitude']['gco:Decimal'] . ',' . $geoArray['gmd:westBoundLongitude']['gco:Decimal'] . ' '; 
            $polyCoordinates .= $geoArray['gmd:northBoundLatitude']['gco:Decimal'] . ',' . $geoArray['gmd:west BoundLongitude']['gco:Decimal']; 
        }   
		*/
	
        $instanceType .= '-gmd:EX_BoundingPolygon';
        
        $twigArr = array('instanceName' => $instanceName, 'instanceType' => $instanceType, 'xmlArray' => $xmlArray[0], 'gmlCoordinates' => $gmlCoordinates);
        
        $this->htmlString = $mMD->twig->render('html/EX_BoundingPolygon.html', $twigArr);
		
		$mMD->onReady .= $mMD->twig->render('js/EX_BoundingPolygon.js', array('instanceName' => $instanceName));
        
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