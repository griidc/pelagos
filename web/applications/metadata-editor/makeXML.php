<?php
// @codingStandardsIgnoreFile

function makeXML($xml)
{
    echo '<pre>';
    var_dump($_POST);
    echo '</pre>';

    //exit;

    $xmldocstring = base64_decode($_POST["__ldxmldoc"]);
    $doc = null;

    if ($xmldocstring <> false) {
        $doc = new DomDocument('1.0', 'UTF-8');
        $doc->loadXML($xmldocstring);
    }

    if (isset($_POST['__validated']) and $_POST['__validated'] == 1) {
        createNodesXML($xml, $doc, true);
    } else {
        createNodesXML($xml, $doc, false);
    }

    $dMessage = 'Succesfully Created XML file:';
    drupal_set_message($dMessage, 'status');
}

function createBlankXML()
{
    $doc = new DomDocument('1.0', 'UTF-8');

    $root = createXmlNode($doc, $doc, 'gmi:MI_Metadata');

    $root->setAttribute('xmlns', 'http://www.isotc211.org/2005/gmi');
    $root->setAttribute('xmlns:gco', 'http://www.isotc211.org/2005/gco');
    $root->setAttribute('xmlns:gmd', 'http://www.isotc211.org/2005/gmd');
    $root->setAttribute('xmlns:gmi', 'http://www.isotc211.org/2005/gmi');
    $root->setAttribute('xmlns:gml', 'http://www.opengis.net/gml/3.2');
    $root->setAttribute('xmlns:gmx', 'http://www.isotc211.org/2005/gmx');
    $root->setAttribute('xmlns:gsr', 'http://www.isotc211.org/2005/gsr');
    $root->setAttribute('xmlns:gss', 'http://www.isotc211.org/2005/gss');
    $root->setAttribute('xmlns:gts', 'http://www.isotc211.org/2005/gts');
    $root->setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
    $root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
    $root->setAttribute(
        'xsi:schemaLocation',
        'http://www.isotc211.org/2005/gmi https://www.ngdc.noaa.gov/metadata/published/xsd/schema.xsd'
    );

    $doc->normalizeDocument();
    $doc->formatOutput = true;

    $xmlString = $doc->saveXML();
    $newdoc = new DomDocument('1.0', 'UTF-8');

    $newdoc->loadXML($xmlString);

    return $newdoc;
}

function createNodesXML($xml, $doc, $validated)
{

    if (is_null($doc)) {
        $doc = createBlankXML();
    }

    $root = $doc->firstChild;
    $parent = $doc;

    $now = date('c');

    // $xmlComment = "Created with GRIIDC Metadata Editor 14.08 on $now";
    // $commentNode = $doc->createComment($xmlComment);
    // $commentNode = $doc->appendChild($commentNode);

    $xpathdoc = new DOMXpath($doc);

    foreach ($xml as $key => $val) {
        $xpath = "";

        if (substr($key, 0, 2) != "__") {
            $nodelevels = preg_split("/-/", $key);

            foreach ($nodelevels as $nodelevel) {
                $splitnodelevel = preg_split("/\!/", $nodelevel);

                $xpath .= "/" . $splitnodelevel[0] . '[1]';
            }

            $xpathdoc = new DOMXpath($doc);
            $elements = $xpathdoc->query($xpath);

            if ($elements->length > 0) {
                $node = $elements->item(0);
                $parent = $node->parentNode;
                $val = htmlspecialchars($val, ENT_QUOTES | 'ENT_XML1', 'UTF-8');

                #here then!
                if ($parent->nodeName == 'gmd:deliveryPoint') {
                    $parent->removeChild($node);

                    $node = $doc->createElement('gco:CharacterString');
                    $node = $parent->appendChild($node);

                    $cdata = $doc->createCDATASection($val);
                    $node = $node->appendChild($cdata);
                } else {
                    //$val = htmlspecialchars($val, ENT_QUOTES | 'ENT_XML1', 'UTF-8');
                    $node->nodeValue = $val;
                }

                $parent = $node->parentNode;
                if ($parent->nodeName == 'gmd:fileIdentifier') {
                    $val = str_replace(':', '-', $val);
                }
                echo "($val) Existing Parent:" . $parent->nodeName . '<br>';
            } else {
                $nodelevels = preg_split("/\//", $xpath);

                $xpath = "";

                $parent = $doc;

                foreach ($nodelevels as $nodelevel) {
                    if ($nodelevel <> "") {
                        $xpath .= "/" . $nodelevel;
                        $elements = $xpathdoc->query($xpath);

                        echo 'now working: ';
                        echo $xpath . '<br>';

                        //var_dump($elements);

                        if ($elements->length > 0) {
                            $xpathdocsub = new DOMXpath($doc);
                            $subelements = $xpathdocsub->query($xpath);
                            $node = $subelements->item(0);
                            //$val = htmlspecialchars($val, ENT_QUOTES | 'ENT_XML1', 'UTF-8');
                            //$node->nodeValue = $val;
                            $parent = $node->parentNode;
                            $parentname = $parent->nodeName;
                            $parentXpath = $xpath;
                            echo "Found path: $xpath. Parent is: " . $parentname . "<br>";

                            addNodeAttributes($doc, $parent, $node, $nodelevel, $val);
                        } else {
                            $xpathdocsub = new DOMXpath($doc);
                            $subelements = $xpathdocsub->query($parentXpath);
                            $node = $subelements->item(0);
                            $parent = $node;
                            $parentname = $parent->nodeName;

                            echo "Found last path: $parentXpath. Old parent is: " . $parentname . "<br>";

                            ## This section Abadoned for now!!!!

                            #find parent by previous xpath:
                            $thisPath = substr(str_replace('/', '-', $xpath), 1);
                            //$thosePaths = preg_split('/-/', $thisPath, 2    );
                            //$thePath = $thosePaths[1];
                            //var_dump(array_keys($xml));
                            $thisIndex =  array_search($thisPath, array_keys($xml));
                            /*
                            if ($thisIndex > 0)
                            {
                                $keylist = array_keys($xml);
                                $prevkey = $keylist[$thisIndex-1];
                                $previousXpath = "";
                                $nodelevels = preg_split("/-/",$prevkey);
                                foreach ($nodelevels as $nodelevel)
                                {
                                    $previousXpath .= "/" . $nodelevel;
                                }

                                echo "###$previousXpath";
                                $subelements = $xpathdocsub->query($previousXpath);
                                $prevnode = $subelements->item(0);
                                echo "Will it go into node:" . $prevnode->nodeName;
                            }
                            */
                            ##

                            //echo "!!!$thisPath:nowpath:$thisIndex Result:";


                            if ($thisIndex > 0) {
                                $node = addXMLChildValue($doc, $parent, $nodelevel, $val);
                            } else {
                                $nodelevel = preg_split("/\[.\]/", $nodelevel)[0];
                                $node = $doc->createElement($nodelevel);
                                $node = $parent->appendChild($node);
                                addNodeAttributes($doc, $parent, $node, $nodelevel, $val);
                            }



                            echo "Addded Node: $nodelevel<br>";

                            $parentXpath = $xpath;

                            //$doc->formatOutput = true;
                            //$doc->normalizeDocument();
                            $xmlString = $doc->saveXML();

                            $doc->loadXML($xmlString);
                            $doc->normalizeDocument();
                        }

                        //addNodeAttributes($doc,$parent,$node,$nodelevel,$val);
                    }

                }

                $xpathdocsub = new DOMXpath($doc);
                $subelements = $xpathdocsub->query($parentXpath);
                if ($subelements <> false) {
                    $node = $subelements->item(0);
                    if ($node != null) {
                        $parent = $node->parentNode;
                        $grandparent = $parent->parentNode;
                        $nodelevel = $parent->nodeName;

                        if ($node->nodeName == 'gmd:polygon') {
                            echo "it here $val";
                        }

                        if ($parent->nodeName == 'gmd:deliveryPoint') {
                            $cdata = $doc->createCDATASection($val);
                            $node = $node->appendChild($cdata);
                        } else {
                            //$val = htmlspecialchars($val, ENT_QUOTES | 'ENT_XML1', 'UTF-8');
                            $node->nodeValue = $val;
                        }
                    }

                    //addNodeAttributes($doc,$parent,$node,$nodelevel,$val);
                    //addNodeAttributes($doc,$grandparent,$parent,$nodelevel,$val);
                }

                $parent = $doc;
            }
        }

        $filename = $xml["gmi:MI_Metadata-gmd:fileIdentifier-gco:CharacterString"];

    }

    // Add Maintenance Note
    $miXpathdoc = new DOMXpath($doc);
    $parentMi = $miXpathdoc->query('/gmi:MI_Metadata/gmd:metadataMaintenance/gmd:MD_MaintenanceInformation');

    $currentNode = 0;
    if ($parentMi->item(0)) {
        $currentNode = $parentMi->item(0);
        $newNode = $doc->createElement('gmd:maintenanceNote');
        $currentNode = $currentNode->appendChild($newNode);
        $maintNoteNode = $currentNode;
    } else {
        $parent = $miXpathdoc->query('/gmi:MI_Metadata');
        $currentNode = createXmlNode($doc, $parent->item(0), 'gmd:metadataMaintenance');
        $currentNode = createXmlNode($doc, $currentNode, 'gmd:MD_MaintenanceInformation');
        $maintInfoNode = $currentNode;

        $newNode = $doc->createElement('gmd:maintenanceAndUpdateFrequency');
        addNodeAttributes($doc, $newNode->parentNode, $newNode, 'gco:nilReason', 'unknown');
        $currentNode->appendChild($newNode);
        $freqNode = $currentNode;

        $currentNode = createXmlNode($doc, $maintInfoNode, 'gmd:maintenanceNote');
        $maintNoteNode = $currentNode;
    }

    $timestamp = gmdate('c');
    $maintenanceNote='';
    if ($validated == true) {
        $maintenanceNote = "This ISO metadata record was created using the 'Check and Save to File' ";
        $maintenanceNote .= "(with form validation) function of the GRIIDC ISO 19115-2 Metadata Editor on $timestamp";
    } else {
        $maintenanceNote = "This ISO metadata record was created using the 'Save to File' (no form validation) ";
        $maintenanceNote .= "function of the GRIIDC ISO 19115-2 Metadata Editor on $timestamp";
    }

    $newChild = $doc->createElement('gco:CharacterString', $maintenanceNote);
    $maintNoteNode->appendChild($newChild);

    $doc->formatOutput = true;
    $doc->normalizeDocument();
    $xmlString = $doc->saveXML();

    $tidy_config = array('indent' => true,'indent-spaces' => 4,'input-xml' => true,'output-xml' => true,'wrap' => 0);

    $tidy = new tidy;
    $tidy->parseString($xmlString, $tidy_config, 'utf8');
    $tidy->cleanRepair();

    header("Content-type: text/xml; charset=utf-8");
    header("Content-Disposition: attachment; filename=$filename");

    ob_clean();
    flush();
    //echo $doc->saveXML();
    echo $tidy;
    exit;
    //*/
}

function addXMLChildValue($doc, $parent, $fieldname, $fieldvalue)
{
    echo "Doing $fieldname";
    $escfieldvalue = htmlspecialchars($fieldvalue, ENT_QUOTES | 'ENT_XML1', 'UTF-8');
    $child = $doc->createElement($fieldname);
    $child = $parent->appendChild($child);
    $value = $doc->createTextNode($escfieldvalue);
    $value = $child->appendChild($value);

    addNodeAttributes($doc, $parent, $child, $fieldname, $fieldvalue);

    return $child;
}

function createXmlNode($doc, $parent, $nodeName)
{
    //echo $nodeName;
    $node = $doc->createElement($nodeName);
    $node = $parent->appendChild($node);

    addNodeAttributes($doc, $parent, $node, $nodeName);

    return $node;
}

function codeLookup($codeList, $codeListValue)
{
    #TODO

    $mMD = new metaData();

    $myIni = $mMD->loadINI('codeSpace.ini');

    return $myIni[$codeList][$codeListValue];

    //return '000';

}

function addNodeAttributes($doc, $parent, $node, $fieldname, $fieldvalue = null)
{
    switch ($fieldname) {
        case 'gco:nilReason':
            $node->setAttribute('gco:nilReason', $fieldvalue);
            break;
        case '-gmd:fileIdentifier':
            $node->nodeValue = str_replace(':', '-', $fieldvalue);
            break;
        case 'gmd:MD_CharacterSetCode':
            $node->setAttribute(
                'codeList',
                'http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#MD_CharacterSetCode'
            );
            $node->setAttribute('codeListValue', $fieldvalue);
            $codeSpace = codeLookup($fieldname, $fieldvalue);
            $node->setAttribute('codeSpace', $codeSpace);
            break;
        case 'gmd:MD_ScopeCode':
            $node->setAttribute(
                'codeList',
                'http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#MD_ScopeCode'
            );
            $node->setAttribute('codeListValue', $fieldvalue);
            $codeSpace = codeLookup($fieldname, $fieldvalue);
            $node->setAttribute('codeSpace', $codeSpace);
            break;
        case 'gmd:CI_RoleCode':
            $node->setAttribute(
                'codeList',
                'http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_RoleCode'
            );
            $node->setAttribute('codeListValue', $fieldvalue);
            $codeSpace = codeLookup($fieldname, $fieldvalue);
            $node->setAttribute('codeSpace', $codeSpace);
            break;
        case 'gmd:CI_DateTypeCode':
            $node->setAttribute(
                'codeList',
                'http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_DateTypeCode'
            );
            $node->setAttribute('codeListValue', $fieldvalue);
            $codeSpace = codeLookup($fieldname, $fieldvalue);
            $node->setAttribute('codeSpace', $codeSpace);
            break;
        case 'gmd:MD_ProgressCode':
            $node->setAttribute(
                'codeList',
                'http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#MD_ProgressCode'
            );
            $node->setAttribute('codeListValue', $fieldvalue);
            $codeSpace = codeLookup($fieldname, $fieldvalue);
            $node->setAttribute('codeSpace', $codeSpace);
            break;
        case 'gmd:MD_KeywordTypeCode':
            $node->setAttribute(
                'codeList',
                'http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#MD_KeywordTypeCode'
            );
            $node->setAttribute('codeListValue', $fieldvalue);
            $codeSpace = codeLookup($fieldname, $fieldvalue);
            $node->setAttribute('codeSpace', $codeSpace);
            break;
        case 'gmd:description':
            #Move up a few parent nodes to remove EX_Extent
            $newParent = $parent->parentNode;

            #Here some node needs to removed and eventually re-created.
            $newParent->removeChild($parent);

            #Recreating the node EX_Extent
            $node = $doc->createElement('gmd:EX_Extent');
            $node = $newParent->appendChild($node);
            $node->setAttribute('id', 'descriptiveExtent');

            $parent = $node;

            $node = $doc->createElement('gmd:description');
            $node = $parent->appendChild($node);

            break;
        case 'gmd:polygons':
            #Move up a few parent nodes to remove EX_Extent
            $newParent = $parent->parentNode;
            $grandParent = $newParent->parentNode;
            $superParent = $grandParent->parentNode;

            #Here some nodes need to removed and eventually re-created.
            $superParent->removeChild($grandParent);

            #Creating childs nodes from EX_Extent trough gmd:polygon
            $node = $doc->createElement('gmd:EX_Extent');
            $node = $superParent->appendChild($node);
            $node->setAttribute('id', 'boundingExtent');

            $parent = $node;

            $node = $doc->createElement('gmd:geographicElement');
            $node = $parent->appendChild($node);

            $parent = $node;

            $node = $doc->createElement('gmd:EX_BoundingPolygon');
            $node = $parent->appendChild($node);

            $parent = $node;

            $node = $doc->createElement('gmd:polygon');
            $node = $parent->appendChild($node);

            $fieldvalue = htmlspecialchars_decode($fieldvalue, ENT_NOQUOTES | 'ENT_XML1');

            #Don't do this is there is no GML, or it will fail.
            if ($fieldvalue != '') {
                $polygonDoc = new DomDocument('1.0', 'UTF-8');
                $polygonDoc->loadXML($fieldvalue, LIBXML_NOERROR);

                $polygonNode = $polygonDoc->documentElement;

                if ($polygonNode instanceof DOMNode == true) {
                    $node->appendChild($doc->importNode($polygonNode, true));
                }
            }
            break;
        case 'gml:posList':
            $node->setAttribute('srsDimension', '2');
            break;
        case 'gml:Polygon':
            $node->setAttribute('gml:id', 'Polygon');
            $node->setAttribute('srsName', 'urn:ogc:def:crs:EPSG::4326');
            break;
        case 'gmd:EX_GeographicBoundingBox':
            $node->setAttribute('id', 'boundingGeographicBoundingBox');
            break;
        case 'gml:TimePeriod':
            $node->setAttribute('gml:id', 'boundingTemporalExtent');
            break;
        case 'gmd:version':
            if ($fieldvalue == "") {
                $node->setAttribute('gco:nilReason', 'inapplicable');
            }
            // $child = $node->firstChild;
            // if (isset($child))
            // {
                // $node->removeChild($child);
            // }

            break;
        case 'gmd:CI_DateTypeCode':
            $node->setAttribute(
                'codeList',
                'http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_DateTypeCode'
            );
            $node->setAttribute('codeListValue', $fieldvalue);
            $codeSpace = codeLookup($fieldname, $fieldvalue);
            $node->setAttribute('codeSpace', $codeSpace);
            break;
        case '!gmd:descriptiveKeywords':
            $elements2 = null;
            $beforeXpath = "/gmi:MI_Metadata/gmd:identificationInfo/gmd:MD_DataIdentification/gmd:language";
            if ($doc != null) {
                $xpathdoc2 = new DOMXpath($doc);
                $elements2 = $xpathdoc2->query($beforeXpath);
                $beforeNode = $elements2->item(0);

                if ($element2->length > 0) {
                    $parent->removeChild($node);
                    $parent = $beforeNode->parentNode;

                    $node = $doc->createElement('gmd:descriptiveKeywords');
                    $node = $parent->insertBefore($node, $beforeNode);
                }
            }
            break;
        case 'gmd:topicCategory':
            $beforeXpath = '/gmi:MI_Metadata/gmd:identificationInfo/gmd:MD_DataIdentification/gmd:extent';
            $xpathdoc2 = new DOMXpath($doc);
            $elements2 = $xpathdoc2->query($beforeXpath);
            $beforeNode = $elements2->item(0);
            if ($element2->length > 0) {
                $parent->removeChild($node);

                $parent = $beforeNode->parentNode;

                $node = $doc->createElement('gmd:topicCategory');
                $node = $parent->insertBefore($node, $beforeNode);
            }
            break;
        case 'gml:coordinates':
            $searchXpath = '/gmi:MI_Metadata/gmd:identificationInfo/gmd:MD_DataIdentification/gmd:extent/';
            $searchXpath .= 'gmd:EX_Extent/gmd:geographicElement/gmd:EX_GeographicBoundingBox';
            $xpathdoc2 = new DOMXpath($doc);
            $elements2 = $xpathdoc2->query($searchXpath);
            $node = $elements2->item(0);
            if ($elements2->length > 0) {
                $parent = $node->parentNode;
                $parent->removeChild($node);
            }

            break;
        case 'gmd:keywordtheme':
            $parent->removeChild($node);

            $elements2 = null;
            $beforeXpath = '/gmi:MI_Metadata/gmd:identificationInfo[1]/gmd:MD_DataIdentification[1]/';
            $beforeXpath .= 'gmd:descriptiveKeywords[1]/gmd:MD_Keywords[1]/gmd:type[1]/gmd:MD_KeywordTypeCode[1]';

            if ($doc != null) {
                $xpathdoc2 = new DOMXpath($doc);
                $elements2 = $xpathdoc2->query($beforeXpath);
                $beforeNode = $elements2->item(0);

                //var_dump($beforeNode->nodeName);
                //var_dump($elements2->length);

                //var_dump($beforeNode->textContent);

                if ($elements2->length > 0 and $beforeNode->textContent == "theme") {
                    $parent = $beforeNode->parentNode;
                    $parent = $parent->parentNode;
                    $node = $parent->parentNode;
                    $parent = $node->parentNode;

                    echo $node->nodeName.'!!<br>';
                    $parent->removeChild($node);

                    $xmlString = $doc->saveXML();

                    $doc->loadXML($xmlString);
                    //$doc->normalizeDocument();

                } else {
                    //echo $parent->nodeName;
                }
            }


            $elements2 = null;
            $beforeXpath = "/gmi:MI_Metadata/gmd:identificationInfo/gmd:MD_DataIdentification/gmd:language";
            if ($doc != null) {
                $xpathdoc2 = new DOMXpath($doc);
                $elements2 = $xpathdoc2->query($beforeXpath);
                $beforeNode = $elements2->item(0);

                //var_dump($beforeNode->nodeName);

                if ($elements2->length > 0) {
                    //$parent->removeChild($node);
                    $parent = $beforeNode->parentNode;

                    $node = $doc->createElement('gmd:descriptiveKeywords');
                    $node = $parent->insertBefore($node, $beforeNode);
                } else {
                    $node = $doc->createElement('gmd:descriptiveKeywords');
                    $node = $parent->appendChild($node);
                }
            }

            #go back two nodes
            //$newparent = $parent->parentNode;
            //$parent = $newparent->parentNode;

            #make another descriptiveKeywords node
            //$node = $doc->createElement('gmd:descriptiveKeywords');
            //$node = $parent->appendChild($node);
            #make another MD_Keywords node
            $parent = $doc->createElement('gmd:MD_Keywords');
            $parent = $node->appendChild($parent);

            $arrkeywords = preg_split("/\;/", $fieldvalue);

            foreach ($arrkeywords as $myKeywords) {
                $mdkwrd = $doc->createElement('gmd:keyword');
                $mdkwrd = $parent->appendChild($mdkwrd);

                $addnode = addXMLChildValue($doc, $mdkwrd, 'gco:CharacterString', $myKeywords);
            }

            $tpkwrd = $doc->createElement('gmd:type');
            $tpkwrd = $parent->appendChild($tpkwrd);

            $addnode = addXMLChildValue($doc, $tpkwrd, 'gmd:MD_KeywordTypeCode', 'theme');
            break;
        case 'gmd:keywordplace':
            //var_dump($node->nodeName);
        //    exit;

            $parent->removeChild($node);

            $elements2 = null;
            $beforeXpath = '/gmi:MI_Metadata/gmd:identificationInfo[1]/gmd:MD_DataIdentification[1]/';
            $beforeXpath .= 'gmd:descriptiveKeywords[1]/gmd:MD_Keywords[1]/gmd:type[1]/gmd:MD_KeywordTypeCode[1]';

            if ($doc != null) {
                $xpathdoc2 = new DOMXpath($doc);
                $elements2 = $xpathdoc2->query($beforeXpath);
                $beforeNode = $elements2->item(0);

                //var_dump($beforeNode->nodeName);
                //var_dump($elements2->length);

                //var_dump($beforeNode->textContent);

                if ($elements2->length > 0 and $beforeNode->textContent == "place") {
                    $parent = $beforeNode->parentNode;
                    $parent = $parent->parentNode;
                    $node = $parent->parentNode;
                    $parent = $node->parentNode;

                    echo $node->nodeName.'!!<br>';
                    $parent->removeChild($node);

                    $xmlString = $doc->saveXML();

                    $doc->loadXML($xmlString);
                    //$doc->normalizeDocument();
                } else {
                    //echo $parent->nodeName;
                }
            }

            $elements2 = null;
            $beforeXpath = "/gmi:MI_Metadata/gmd:identificationInfo/gmd:MD_DataIdentification/gmd:language";
            if ($doc != null) {
                $xpathdoc2 = new DOMXpath($doc);
                $elements2 = $xpathdoc2->query($beforeXpath);
                $beforeNode = $elements2->item(0);

                //var_dump($beforeNode->nodeName);

                if ($elements2->length > 0) {
                    //$parent->removeChild($node);
                    $parent = $beforeNode->parentNode;

                    $node = $doc->createElement('gmd:descriptiveKeywords');
                    $node = $parent->insertBefore($node, $beforeNode);
                } else {
                    $node = $doc->createElement('gmd:descriptiveKeywords');
                    $node = $parent->appendChild($node);
                }
            }

            #go back two nodes
            //$newparent = $parent->parentNode;
            //$parent = $newparent->parentNode;

            #make another descriptiveKeywords node
            //$dsckwrd = $doc->createElement('gmd:descriptiveKeywords');
            //$dsckwrd = $parent->appendChild($dsckwrd);
            #make another MD_Keywords node
            $parent = $doc->createElement('gmd:MD_Keywords');
            $parent = $node->appendChild($parent);

            $arrkeywords = preg_split("/\;/", $fieldvalue);

            if ($fieldvalue == "") {
                $mdkwrd = $doc->createElement('gmd:keyword');
                $mdkwrd = $parent->appendChild($mdkwrd);
                $mdkwrd->setAttribute('gco:nilReason', 'inapplicable');
            } else {
                foreach ($arrkeywords as $myKeywords) {
                    $mdkwrd = $doc->createElement('gmd:keyword');
                    $mdkwrd = $parent->appendChild($mdkwrd);
                    $addnode = addXMLChildValue($doc, $mdkwrd, 'gco:CharacterString', $myKeywords);
                }
            }

            $tpkwrd = $doc->createElement('gmd:type');
            $tpkwrd = $parent->appendChild($tpkwrd);

            $addnode = addXMLChildValue($doc, $tpkwrd, 'gmd:MD_KeywordTypeCode', 'place');

            break;
        case 'gmd:topicCategorys':
            #todo: remove all old gmd:topicCategory
            # insert before <gmd:extent>
            $parent->removeChild($node);

            $elements2 = null;
            $beforeXpath = '/gmi:MI_Metadata/gmd:identificationInfo/gmd:MD_DataIdentification/gmd:topicCategory';
            $xpathdoc2 = new DOMXpath($doc);
            $elements2 = $xpathdoc2->query($beforeXpath);

            foreach ($elements2 as $node) {
                $parent = $node->parentNode;
                $parent->removeChild($node);
            }

            $arrkeywords = preg_split("/\;/", $fieldvalue);

            $elements2 = null;
            $beforeXpath = '/gmi:MI_Metadata/gmd:identificationInfo/gmd:MD_DataIdentification[1]/gmd:extent';
            $xpathdoc2 = new DOMXpath($doc);
            $elements2 = $xpathdoc2->query($beforeXpath);

            foreach ($arrkeywords as $myKeywords) {
                if ($elements2->length > 0) {
                    $beforeNode = $elements2->item(0);
                    $parent = $beforeNode->parentNode;

                    $node = $doc->createElement('gmd:topicCategory');
                    $node = $parent->insertBefore($node, $beforeNode);
                } else {
                    $node = $doc->createElement('gmd:topicCategory');
                    $node = $parent->appendChild($node);
                }

                $addnode = addXMLChildValue($doc, $node, 'gmd:MD_TopicCategoryCode', $myKeywords);
                //$child = $doc->createElement('gmd:MD_TopicCategoryCode');
                //$child = $parent->appendChild($child);
                //$value = $doc->createTextNode($myKeywords);
                //$value = $child->appendChild($value);
            }
            break;
    }
}

function guid()
{
    if (function_exists('com_create_guid')) {
        return com_create_guid();
    } else {
        mt_srand((double)microtime()*10000); //optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45); // "-"
        $uuid = chr(123)// "{"
        .substr($charid, 0, 8).$hyphen
        .substr($charid, 8, 4).$hyphen
        .substr($charid, 12, 4).$hyphen
        .substr($charid, 16, 4).$hyphen
        .substr($charid, 20, 12)
        .chr(125);// "}"
        return $uuid;
    }
}

function removeFromArray($array, $position)
{
    $temparr = array();

    for ($i=0; $i <= count($array)-1; $i++) {
        if ($i<>$position) {
            $temparr[] = $array[$i];
        }
    }

    return $temparr;
}

function insertIntoArray($array, $position, $var)
{
    $temparr = array();

    $i = 0;

    if ($position>count($array)) {
        $temparr = $array;
        array_push($temparr, $var);
    } else {
        foreach ($array as $node) {
            if ($i==$position) {
                $temparr[] = $var;
            } else {
                $temparr[] = $node;
            }
            $i++;
        }
    }
    return $temparr;
}
