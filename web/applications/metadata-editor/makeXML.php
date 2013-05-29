<?php

function makeXML($xml)
{
	echo '<pre>';
	var_dump($_POST);
	echo '</pre>';
	
	//exit;
	
	#array_shift($xml); #To get rid of the Q from Drupal (_GET ONLY!)
		
	createNodesXML($xml);
}

function createBlankXML()
{
	$doc = new DomDocument('1.0','UTF-8');
	
	$root = createXmlNode($doc,$doc,'gmi:MI_Metadata');
	
	$root->setAttribute('xmlns','http://www.isotc211.org/2005/gmi');
	$root->setAttribute('xmlns:gco','http://www.isotc211.org/2005/gco');
	$root->setAttribute('xmlns:gmd','http://www.isotc211.org/2005/gmd');
	$root->setAttribute('xmlns:gmi','http://www.isotc211.org/2005/gmi');
	$root->setAttribute('xmlns:gml','http://www.opengis.net/gml/3.2');
	$root->setAttribute('xmlns:gmx','http://www.isotc211.org/2005/gmx');
	$root->setAttribute('xmlns:gsr','http://www.isotc211.org/2005/gsr');
	$root->setAttribute('xmlns:gss','http://www.isotc211.org/2005/gss');
	$root->setAttribute('xmlns:gts','http://www.isotc211.org/2005/gts');
	$root->setAttribute('xmlns:xlink','http://www.w3.org/1999/xlink');
	$root->setAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
	$root->setAttribute('xsi:schemaLocation','http://www.isotc211.org/2005/gmi http://www.ngdc.noaa.gov/metadata/published/xsd/schema.xsd');
	
	return $doc;
}

function createNodesXML($doc,$xml)
{
	
	foreach ($xml as $key=>$val)
	{

	
	}
	
}

function createXmlNode($doc,$parent,$nodeName)
{
	//echo $nodeName;
	$node = $doc->createElement($nodeName);
	$node = $parent->appendChild($node);
	
	addNodeAttributes($doc,$parent,$node,$nodeName);
	
	return $node;
}

function codeLookup($codeList,$codeListValue)
{
	#TODO
	return '000'; 
	
}

function addNodeAttributes($doc,$parent,&$node,$fieldname,$fieldvalue="")
{
	switch ($fieldname)
	{
		case 'gmd:MD_CharacterSetCode':
		{
			$node->setAttribute('codeList','http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#MD_CharacterSetCode');
			$node->setAttribute('codeListValue',$fieldvalue);
			$codeSpace = codeLookup($fieldname,$fieldvalue);
			$node->setAttribute('codeSpace',$codeSpace);
			break;
		}
		case 'gmd:MD_ScopeCode':
		{
			$node->setAttribute('codeList','http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#MD_ScopeCode');
			$node->setAttribute('codeListValue',$fieldvalue);
			$codeSpace = codeLookup($fieldname,$fieldvalue);
			$node->setAttribute('codeSpace',$codeSpace);
			break;
		}
		case 'gmd:CI_RoleCode':
		{
			$node->setAttribute('codeList','http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_RoleCode');
			$node->setAttribute('codeListValue',$fieldvalue);
			$codeSpace = codeLookup($fieldname,$fieldvalue);
			$node->setAttribute('codeSpace',$codeSpace);
			break;
		}
		case 'gmd:CI_DateTypeCode':
		{
			$node->setAttribute('codeList','http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_DateTypeCode');
			$node->setAttribute('codeListValue',$fieldvalue);
			$codeSpace = codeLookup($fieldname,$fieldvalue);
			$node->setAttribute('codeSpace',$codeSpace);
			break;
		}
		case 'gmd:MD_ProgressCode':
		{
			$node->setAttribute('codeList','http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#MD_ProgressCode');
			$node->setAttribute('codeListValue',$fieldvalue);
			$codeSpace = codeLookup($fieldname,$fieldvalue);
			$node->setAttribute('codeSpace',$codeSpace);
			break;
		}
		case 'gmd:MD_KeywordTypeCode':
		{
			$node->setAttribute('codeList','http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#MD_KeywordTypeCode');
			$node->setAttribute('codeListValue',$fieldvalue);
			$codeSpace = codeLookup($fieldname,$fieldvalue);
			$node->setAttribute('codeSpace',$codeSpace);
			break;
		}
		case 'gmd:MD_KeywordTypeCode':
		{
			$node->setAttribute('codeList','http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#MD_KeywordTypeCode');
			$node->setAttribute('codeListValue',$fieldvalue);
			$codeSpace = codeLookup($fieldname,$fieldvalue);
			$node->setAttribute('codeSpace',$codeSpace);
			break;
		}
		case 'gmd:EX_Extent':
		{
			$node->setAttribute('id','boundingExtent');
			break;
		}
		case 'gmd:EX_GeographicBoundingBox':
		{
			$node->setAttribute('id','boundingGeographicBoundingBox');
			break;
		}
		case 'gml:TimePeriod':
		{
			$node->setAttribute('gml:id','boundingTemporalExtent');
			break;
		}
		case 'gmd:CI_DateTypeCode':
		{
			$node->setAttribute('codeList','http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_DateTypeCode');
			$node->setAttribute('codeListValue',$fieldvalue);
			$codeSpace = codeLookup($fieldname,$fieldvalue);
			$node->setAttribute('codeSpace',$codeSpace);
			break;
		}
		
		case 'gmd:keyword':
		{
			$parent->removeChild($node);
			
			$arrkeywords = preg_split("/\;/",$fieldvalue);
			
			foreach ($arrkeywords as $myKeywords)
			{
				$mdkwrd = $doc->createElement($fieldname);
				$mdkwrd = $parent->appendChild($mdkwrd);
				$addnode = addXMLChildValue($doc,$mdkwrd,"gco:CharacterString",$myKeywords);
			}
			break;
		}
		
		case 'gmd:MD_TopicCategoryCode':
		{
			$parent->removeChild($node);
			
			$arrkeywords = preg_split("/\;/",$fieldvalue);
			
			foreach ($arrkeywords as $myKeywords)
			{
				$child = $doc->createElement('gmd:MD_TopicCategoryCode');
				$child = $parent->appendChild($child);
				$value = $doc->createTextNode($myKeywords);
				$value = $child->appendChild($value);
			}
			break;
		}
	}
}

function addXMLChildValue($doc,$parent,$fieldname,$fieldvalue)
{
	$fieldvalue = htmlspecialchars($fieldvalue, ENT_QUOTES | 'ENT_XML1', 'UTF-8');
	$child = $doc->createElement($fieldname);
	$child = $parent->appendChild($child);
	$value = $doc->createTextNode($fieldvalue);
	$value = $child->appendChild($value);
	
	addNodeAttributes($doc,$parent,$child,$fieldname,$fieldvalue);
	
	return $child;
}



function createNodesXML_old($xml)
{
	$doc = new DomDocument('1.0','UTF-8');
	
	$root = createXmlNode($doc,$doc,'gmi:MI_Metadata');
	
	$root->setAttribute('xmlns','http://www.isotc211.org/2005/gmi');
	$root->setAttribute('xmlns:gco','http://www.isotc211.org/2005/gco');
	$root->setAttribute('xmlns:gmd','http://www.isotc211.org/2005/gmd');
	$root->setAttribute('xmlns:gmi','http://www.isotc211.org/2005/gmi');
	$root->setAttribute('xmlns:gml','http://www.opengis.net/gml/3.2');
	$root->setAttribute('xmlns:gmx','http://www.isotc211.org/2005/gmx');
	$root->setAttribute('xmlns:gsr','http://www.isotc211.org/2005/gsr');
	$root->setAttribute('xmlns:gss','http://www.isotc211.org/2005/gss');
	$root->setAttribute('xmlns:gts','http://www.isotc211.org/2005/gts');
	$root->setAttribute('xmlns:xlink','http://www.w3.org/1999/xlink');
	$root->setAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
	$root->setAttribute('xsi:schemaLocation','http://www.isotc211.org/2005/gmi http://www.ngdc.noaa.gov/metadata/published/xsd/schema.xsd');
	
	$parent = $root;
	
	$nodeinstance;
	
	foreach ($xml as $key=>$val)
	{
		$nodelevels = preg_split("/-/",$key);
		
		$nodecnt = count($nodelevels);
		
		$icnt=0;
		
		//var_dump($nodelevels);
		
		foreach ($nodelevels as $nodelevel)
		{
			$splitlevel = $nodelevel;
			$splitnodelevel = preg_split("/\!/",$splitlevel);
			
			//var_dump($splitnodelevel);
			
			if (isset($splitnodelevel[1]))
			{
				$nodeinstance =  '_'.$splitnodelevel[1];
			}
			
			$nodelevel = $splitnodelevel[0];
			
			//var_dump($nodeinstance);
						
			$icnt++;
			if ($icnt == $nodecnt)
			{
				#makechild
				$nodename = str_replace(":","_",$nodelevels[$nodecnt-2]);
				if (isset($nodeinstance))
				{
					$nodevar = 'node_'.$nodeinstance.'_'.$nodename;
				}
				else
				{
					$nodevar = 'node_'.$nodename;
				}
				//var_dump($nodevar);
				$thisparent = ${$nodevar};
				addXMLChildValue($doc,$thisparent,$nodelevel,$val);
			}
			else
			{
				#makenode
				$nodename = str_replace(":","_",$nodelevel);
				if (isset($nodeinstance))
				{
					$nodevar = 'node_'.$nodeinstance.'_'.$nodename;
				}
				else
				{
					$nodevar = 'node_'.$nodename;
				}
				//var_dump($nodevar);
				if (!isset(${$nodevar}))
				{
					${$nodevar} = createXmlNode($doc,$parent,$nodelevel);
				}
				$parent = ${$nodevar};
			}
		}
		$parent = $root;
		
	}
	
	header("Content-type: text/xml; charset=utf-8"); 
	header('Content-Disposition: attachment; filename=metadata.xml');
		
	$doc->normalizeDocument();
	$doc->formatOutput = true;
	ob_clean();
	flush();
	echo $doc->saveXML();
	exit;
}

function removeFromArray($array,$position)
{
	$temparr = array();
	
	for ($i=0;$i<=count($array)-1;$i++)
	{
		if ($i<>$position)
		{
			$temparr[] = $array[$i];
		}
	}
	
	return $temparr;
}

function insertIntoArray($array,$position,$var)
{
	$temparr = array();
	
	$i = 0;
	
	if ($position>count($array))
	{
		$temparr = $array;
		array_push($temparr,$var);
	}
	else
	{
		
		foreach ($array as $node)
		{
			if ($i==$position)
			{
				$temparr[] = $var;
				
			}
			else
			{
				$temparr[] = $node;
			}
			$i++;
		}
	}
	return $temparr;
}

?>