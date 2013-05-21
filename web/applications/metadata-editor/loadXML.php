<?php

#region
$xmlString = <<<xmldoc
<?xml version="1.0" encoding="UTF-8"?>
<gmi:MI_Metadata xmlns="http://www.isotc211.org/2005/gmi" xmlns:gco="http://www.isotc211.org/2005/gco" xmlns:gmd="http://www.isotc211.org/2005/gmd" xmlns:gmi="http://www.isotc211.org/2005/gmi" xmlns:gml="http://www.opengis.net/gml/3.2" xmlns:gmx="http://www.isotc211.org/2005/gmx" xmlns:gsr="http://www.isotc211.org/2005/gsr" xmlns:gss="http://www.isotc211.org/2005/gss" xmlns:gts="http://www.isotc211.org/2005/gts" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.isotc211.org/2005/gmi http://www.ngdc.noaa.gov/metadata/published/xsd/schema.xsd">
<gmd:fileIdentifier>
<gco:CharacterString>519a4a993feae</gco:CharacterString>
</gmd:fileIdentifier>
<gmd:language>
<gco:CharacterString>eng; USA</gco:CharacterString>
</gmd:language>
<gmd:characterSet>
<gmd:MD_CharacterSetCode codeList="http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#MD_CharacterSetCode" codeListValue="utf8" codeSpace="000">utf8</gmd:MD_CharacterSetCode>
</gmd:characterSet>
<gmd:hierarchyLevel>
<gmd:MD_ScopeCode codeList="http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#MD_ScopeCode" codeListValue="dataset" codeSpace="000">dataset</gmd:MD_ScopeCode>
</gmd:hierarchyLevel>
<gmd:contact>
<gmd:CI_ResponsibleParty>
<gmd:individualName>
<gco:CharacterString>Joe Investigator</gco:CharacterString>
</gmd:individualName>
<gmd:organisationName>
<gco:CharacterString>Texas A&amp;amp;M</gco:CharacterString>
</gmd:organisationName>
<gmd:positionName>
<gco:CharacterString>Head Dude</gco:CharacterString>
</gmd:positionName>
<gmd:contactInfo>
<gmd:CI_Contact>
<gmd:phone>
<gmd:CI_Telephone>
<gmd:voice>
<gco:CharacterString>+1-361-555-1212</gco:CharacterString>
</gmd:voice>
<gmd:facsimile>
<gco:CharacterString>+1-999-765-4321</gco:CharacterString>
</gmd:facsimile>
</gmd:CI_Telephone>
</gmd:phone>
<gmd:address>
<gmd:CI_Address>
<gmd:deliveryPoint>
<gco:CharacterString>6300 Ocean Drive</gco:CharacterString>
</gmd:deliveryPoint>
<gmd:city>
<gco:CharacterString>Corpus Christi</gco:CharacterString>
</gmd:city>
<gmd:administrativeArea>
<gco:CharacterString>Texas</gco:CharacterString>
</gmd:administrativeArea>
<gmd:postalCode>
<gco:CharacterString>78412</gco:CharacterString>
</gmd:postalCode>
<gmd:country>
<gco:CharacterString>USA! USA! USA!</gco:CharacterString>
</gmd:country>
<gmd:electronicMailAddress>
<gco:CharacterString>nomail@here.co.jp</gco:CharacterString>
</gmd:electronicMailAddress>
</gmd:CI_Address>
</gmd:address>
</gmd:CI_Contact>
</gmd:contactInfo>
<gmd:role>
<gmd:CI_RoleCode codeList="http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_RoleCode" codeListValue="CI_RoleCode_principalInvestigator" codeSpace="000">CI_RoleCode_principalInvestigator</gmd:CI_RoleCode>
</gmd:role>
</gmd:CI_ResponsibleParty>
</gmd:contact>
<gmd:contact/>
<gmd:dateStamp>
<gco:Date>2013-05-20</gco:Date>
</gmd:dateStamp>
<gmd:metadataStandardName>
<gco:CharacterString>ISO 19115-2 Geographic Information - Metadata - Part 2: Extensions for Imagery and Gridded Data</gco:CharacterString>
</gmd:metadataStandardName>
<gmd:metadataStandardVersion>
<gco:CharacterString>ISO 19115-2:2009(E)</gco:CharacterString>
</gmd:metadataStandardVersion>
<gmd:identificationInfo>
<gmd:MD_DataIdentification>
<gmd:citation>
<gmd:CI_Citation>
<gmd:title>
<gco:CharacterString>This is the Dataset Title</gco:CharacterString>
</gmd:title>
<gmd:alternateTitle>
<gco:CharacterString>And another title here</gco:CharacterString>
</gmd:alternateTitle>
<gmd:date>
<gmd:CI_Date>
<gmd:date>
<gco:Date>2013-05-01</gco:Date>
</gmd:date>
<gmd:dateType>
<gmd:CI_DateTypeCode codeList="http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_DateTypeCode" codeListValue="publication" codeSpace="000">publication</gmd:CI_DateTypeCode>
</gmd:dateType>
</gmd:CI_Date>
</gmd:date>
</gmd:CI_Citation>
</gmd:citation>
<gmd:abstract>
<gco:CharacterString>Well, what shall I say about this &amp;lt;&amp;lt;dataset&amp;gt;&amp;gt;</gco:CharacterString>
</gmd:abstract>
<gmd:purpose>
<gco:CharacterString>It has none!</gco:CharacterString>
</gmd:purpose>
<gmd:status>
<gmd:MD_ProgressCode codeList="http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#MD_ProgressCode" codeListValue="completed" codeSpace="000">completed</gmd:MD_ProgressCode>
</gmd:status>
<gmd:pointOfContact>
<gmd:CI_ResponsibleParty>
<gmd:individualName>
<gco:CharacterString>Fred Datamanager</gco:CharacterString>
</gmd:individualName>
<gmd:organisationName>
<gco:CharacterString>The other one</gco:CharacterString>
</gmd:organisationName>
<gmd:positionName>
<gco:CharacterString>Data Dude</gco:CharacterString>
</gmd:positionName>
<gmd:contactInfo>
<gmd:CI_Contact>
<gmd:phone>
<gmd:CI_Telephone>
<gmd:voice>
<gco:CharacterString>+1-393-393-9393</gco:CharacterString>
</gmd:voice>
<gmd:facsimile>
<gco:CharacterString>+1-393-400-0303</gco:CharacterString>
</gmd:facsimile>
</gmd:CI_Telephone>
</gmd:phone>
<gmd:address>
<gmd:CI_Address>
<gmd:deliveryPoint>
<gco:CharacterString>123 Melody Lane</gco:CharacterString>
</gmd:deliveryPoint>
<gmd:city>
<gco:CharacterString>New York</gco:CharacterString>
</gmd:city>
<gmd:administrativeArea>
<gco:CharacterString>New York</gco:CharacterString>
</gmd:administrativeArea>
<gmd:postalCode>
<gco:CharacterString>10021</gco:CharacterString>
</gmd:postalCode>
<gmd:country>
<gco:CharacterString>USA</gco:CharacterString>
</gmd:country>
<gmd:electronicMailAddress>
<gco:CharacterString>mymail@issecret.net</gco:CharacterString>
</gmd:electronicMailAddress>
</gmd:CI_Address>
</gmd:address>
</gmd:CI_Contact>
</gmd:contactInfo>
<gmd:role>
<gmd:CI_RoleCode codeList="http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_RoleCode" codeListValue="CI_RoleCode_principalInvestigator" codeSpace="000">CI_RoleCode_principalInvestigator</gmd:CI_RoleCode>
</gmd:role>
</gmd:CI_ResponsibleParty>
</gmd:pointOfContact>
<gmd:descriptiveKeywords>
<gmd:MD_Keywords>
<gmd:keyword>
<gco:CharacterString>data</gco:CharacterString>
</gmd:keyword>
<gmd:keyword>
<gco:CharacterString>more</gco:CharacterString>
</gmd:keyword>
<gmd:keyword>
<gco:CharacterString>things</gco:CharacterString>
</gmd:keyword>
<gmd:type>
<gmd:MD_KeywordTypeCode codeList="http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#MD_KeywordTypeCode" codeListValue="theme" codeSpace="000">theme</gmd:MD_KeywordTypeCode>
</gmd:type>
</gmd:MD_Keywords>
</gmd:descriptiveKeywords>
<gmd:descriptiveKeywords>
<gmd:MD_Keywords>
<gmd:keyword>
<gco:CharacterString>ocean</gco:CharacterString>
</gmd:keyword>
<gmd:keyword>
<gco:CharacterString>water</gco:CharacterString>
</gmd:keyword>
<gmd:keyword>
<gco:CharacterString>deep</gco:CharacterString>
</gmd:keyword>
<gmd:type>
<gmd:MD_KeywordTypeCode codeList="http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#MD_KeywordTypeCode" codeListValue="place" codeSpace="000">place</gmd:MD_KeywordTypeCode>
</gmd:type>
</gmd:MD_Keywords>
</gmd:descriptiveKeywords>
<gmd:topicCategory>
<gmd:MD_TopicCategoryCode>Boundaries</gmd:MD_TopicCategoryCode>
<gmd:MD_TopicCategoryCode>Elevation</gmd:MD_TopicCategoryCode>
<gmd:MD_TopicCategoryCode>Geoscientific Information</gmd:MD_TopicCategoryCode>
<gmd:MD_TopicCategoryCode>Inland Waters</gmd:MD_TopicCategoryCode>
</gmd:topicCategory>
<gmd:language>
<gco:CharacterString>English?</gco:CharacterString>
</gmd:language>
<gmd:extent>
<gmd:EX_Extent id="boundingExtent">
<gmd:description>
<gco:CharacterString>Extent Description</gco:CharacterString>
</gmd:description>
<gmd:geographicElement>
<gmd:EX_GeographicBoundingBox id="boundingGeographicBoundingBox">
<gmd:extentTypeCode>
<gco:Boolean>1</gco:Boolean>
</gmd:extentTypeCode>
<gmd:westBoundLongitude>
<gco:Decimal>-092</gco:Decimal>
</gmd:westBoundLongitude>
<gmd:eastBoundLongitude>
<gco:Decimal>-91</gco:Decimal>
</gmd:eastBoundLongitude>
<gmd:southBoundLatitude>
<gco:Decimal>27</gco:Decimal>
</gmd:southBoundLatitude>
<gmd:northBoundLatitude>
<gco:Decimal>28</gco:Decimal>
</gmd:northBoundLatitude>
</gmd:EX_GeographicBoundingBox>
</gmd:geographicElement>
<gmd:temporalElement>
<gmd:EX_TemporalExtent>
<gmd:extent>
<gml:TimePeriod gml:id="boundingTemporalExtent">
<gml:description>A long time ago...</gml:description>
<gml:beginPosition>2003-05-01</gml:beginPosition>
<gml:endPosition>2013-05-22</gml:endPosition>
</gml:TimePeriod>
</gmd:extent>
</gmd:EX_TemporalExtent>
</gmd:temporalElement>
</gmd:EX_Extent>
</gmd:extent>
<gmd:supplementalInformation>
<gco:CharacterString>None that I can think off.</gco:CharacterString>
</gmd:supplementalInformation>
</gmd:MD_DataIdentification>
</gmd:identificationInfo>
<gmd:identificationInfo/>
<gmd:identificationInfo/>
<gmd:identificationInfo/>
<gmd:identificationInfo/>
<gmd:identificationInfo/>
</gmi:MI_Metadata>
xmldoc;
#end region

/**
 * convert xml string to php array - useful to get a serializable value
	*
 * @param string $xmlstr
 * @return array
	*
 * @author Adrien aka Gaarf & contributors
 * @see http://gaarf.info/2009/08/13/xml-string-to-php-array/
 */

function xmlstr_to_array($xmlstr) {
	$doc = new DOMDocument();
	$doc->loadXML($xmlstr);
	$root = $doc->documentElement;
	$output = domnode_to_array($root);
	$output['@root'] = $root->tagName;
	return $output;
}

function domnode_to_array($node) {
	$output = array();
	switch ($node->nodeType) {
		
		case XML_CDATA_SECTION_NODE:
		case XML_TEXT_NODE:
		$output = trim($node->textContent);
		break;
		
		case XML_ELEMENT_NODE:
		for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) {
			$child = $node->childNodes->item($i);
			$v = domnode_to_array($child);
			if(isset($child->tagName)) {
				$t = $child->tagName;
				if(!isset($output[$t])) {
					$output[$t] = array();
				}
				$output[$t][] = $v;
			}
			elseif($v || $v === '0') {
				$output = (string) $v;
			}
		}
		if($node->attributes->length && !is_array($output)) { //Has attributes but isn't an array
			$output = array('@content'=>$output); //Change output into an array.
		}
		if(is_array($output)) {
			if($node->attributes->length) {
				$a = array();
				foreach($node->attributes as $attrName => $attrNode) {
					$a[$attrName] = (string) $attrNode->value;
				}
				$output['@attributes'] = $a;
			}
			foreach ($output as $t => $v) {
				if(is_array($v) && count($v)==1 && $t!='@attributes') {
					$output[$t] = $v[0];
				}
			}
		}
		break;
	}
	return $output;
}

function loadXML($url)
{
	$doc = new DomDocument('1.0','UTF-8');
	$doc->load($url);
	
	return $doc;
}


function getNodeValue($nodeName,$doc)
{
	$results = array();
	
	$nodes = $doc->getElementsByTagName ($nodeName);
	foreach ($nodes as $node) {
		$nodeValue =  $node->nodeValue;
		$nodePath =  $node->getNodePath();
		array_push($results,array($nodePath => $nodeValue));
	};
	return $results;
}

//$test =  getNodeValue('fileIdentifier',$doc);

?>