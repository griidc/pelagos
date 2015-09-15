<?php
// @codingStandardsIgnoreFile

class EX_BoundingPolygon
{
    private $htmlString;
    
    public function __construct($mMD, $instanceType, $instanceName)
    {
        
        $xmlArray = $mMD->returnPath($instanceType);
             
        $geoArray = $xmlArray[0];
        
        $gmlCoordinates = '';
             
        $gmlCoordinates = $mMD->returnXmlString('/gmi:MI_Metadata/gmd:identificationInfo[1]/gmd:MD_DataIdentification[1]/gmd:extent[1]/gmd:EX_Extent[1]/gmd:geographicElement/gmd:EX_BoundingPolygon[1]/gmd:polygon[1]/*');
              
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
