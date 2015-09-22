<?php
// @codingStandardsIgnoreFile
include 'EX_GeographicBoundingBox.php';
include 'EX_TemporalExtent.php';
include 'EX_BoundingPolygon.php';

class EX_Extent
{
    public function __construct($mMD, $instanceType,$instanceName)
    {
        $xmlArray = $mMD->returnPath($instanceType);
                    
        $instanceType .= "-gmd:EX_Extent!$instanceName";
        
        $geoArray  = false;
        $tempArray = false;
        $extDescText = null;
        
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
            
            if (array_key_exists("gmd:description",$xmlArray[0]))
            {
                $extDescText = $xmlArray[0]["gmd:description"]["gco:CharacterString"];
            }
        }
        
        if ($extDescText != null and $extDescText != "")
        {
            $mMD->onReady .= "changeExtent$instanceName(true);";
        }
        
        $mybpl = new EX_BoundingPolygon($mMD, $instanceType.'-gmd:geographicElement',$instanceName);
        

        //$myggbb = new EX_GeographicBoundingBox($mMD, $instanceType.'-gmd:geographicElement',$instanceName, $geoArray);
        $mytmpext = new EX_TemporalExtent($mMD, $instanceType.'-gmd:temporalElement',$instanceName, $tempArray);
        
        //$GeographicSection = $myggbb->getHTML();
        $GeographicSection = $mybpl->getHTML();
        
        $TemporalExtent = $mytmpext->getHTML();
        
        $twigArr = array('instanceName' => $instanceName, 'instanceType' => $instanceType,'GeographicSection' => $GeographicSection,'TemporalExtent' => $TemporalExtent, 'xmlArray' => $xmlArray[0]);
        
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