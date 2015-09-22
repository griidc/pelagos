<?php
// @codingStandardsIgnoreFile
#include 'CI_Telephone_DL.php';
include_once 'CI_Date.php';
include_once 'CI_ResponsibleParty.php';
include_once 'MD_Identifier.php';

class CI_Citation
{
    private $htmlString;

    public function __construct($mMD, $instanceType, $instanceName,$complex=false,$responsibleparty=false,$iscomplexcitation=false)
    {
        $xmlArray = $mMD->returnPath($instanceType);

        $dateArray = $xmlArray[0]["gmd:date"];

        $mycidateType=$dateArray["gmd:CI_Date"]["gmd:dateType"]["gmd:CI_DateTypeCode"]["@content"];

        if (!isset($mycidateType))
        {$mycidateType = 'publication';};

        $instanceType .= '-gmd:CI_Citation';

        $mycidate = new CI_Date($mMD, $instanceType.'-gmd:date',$instanceName, $dateArray, $mycidateType);

        $Date = $mycidate->getHTML();

        #Citation COULD have an Identifier - NOT BEING USED : TODO
        if ($iscomplexcitation==true)
        {
            $myidentifier = new MD_Identifier($instanceName);
        }

        #Citation COULD have an ResponsibleParty - NOT BEING USED : TODO
        if ($responsibleparty == true)
        {
            $myresp = new CI_ResponsibleParty($instanceName,true);
        }

        $twigArr = array('instanceName' => $instanceName, 'instanceType' => $instanceType,'Date' => $Date,'complex' => $complex, 'xmlArray' => $xmlArray[0]);

        $this->htmlString .= $mMD->twig->render('html/CI_Citation.html', $twigArr);

        return true;
    }

    public function getHTML()
    {
        return $this->htmlString;
    }
}
?>