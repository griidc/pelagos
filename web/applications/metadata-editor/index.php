<?php
//Show Errors (debug only)
error_reporting(-1);
ini_set('display_errors', '1');

if (isset($_GET))


{
	
	//var_dump ($_GET);
	if (count($_GET)>1)
	{
		include 'makeXML.php';
		
		makeXML($_GET);
		exit;
	}
	
	//exit;
	
	echo '<pre>';
	//var_dump($_POST);
	
	echo '</pre>';
}





?>
<style>
input[type=text] {width:400px}
label 
{
	font-weight: bold;
	display: block;
	width: 200px;
	float: left;
}
label:after {content:": "}

select {width:400px}

button {width:50px}
</style>

<fieldset>
<table width="40%,*">
<tr ><td>
<form method="get">

<?php
	include 'MI_Metadata.php';
?>

<input type="submit"/>
<input type="reset"/>

</form>
</td>
<td></td></tr></table>
</fieldset>


<!--

Test URL:

https://proteus.tamucc.edu/~mvandeneijnden/meta/?gmd%3Afileidentifier-gco%3AcharacterString=file_id&gmd%3Alanguage-gco%3AcharacterString=eng%3B+USA&gmd%3AcharacterSet-gmd%3AMD_CharacterSetCode=utf8&gmd%3AhierarchyLevel-gmd%3AMD_ScopeCode=dataset&gmd%3Acontact-gmd%3ACI_ResponsibleParty-gmd%3AindividualName-gco%3ACharacterString=Michael+van+den+Eijnden&gmd%3Acontact-gmd%3ACI_ResponsibleParty-gmd%3AorganisationName-gco%3ACharacterString=Harte&gmd%3Acontact-gmd%3ACI_ResponsibleParty-gmd%3ApositionName-gco%3ACharacterString=Dude&gmd%3Acontact-gmd%3ACI_ResponsibleParty-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aphone-gmd%3ACI_Telephone-gmd%3Avoice-gco%3ACharacterString=361-510-2982&gmd%3Acontact-gmd%3ACI_ResponsibleParty-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aphone-gmd%3ACI_Telephone-gmd%3Afacsimile-gco%3ACharacterString=361-555-1212&gmd%3Acontact-gmd%3ACI_ResponsibleParty-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aaddress-gmd%3ACI_Address-gmd%3AdeliveryPoint-gco%3ACharacterString=Harte&gmd%3Acontact-gmd%3ACI_ResponsibleParty-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aaddress-gmd%3ACI_Address-gmd%3Acity-gco%3ACharacterString=Corpus+Christi&gmd%3Acontact-gmd%3ACI_ResponsibleParty-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aaddress-gmd%3ACI_Address-gmd%3AadministrativeArea-gco%3ACharacterString=Texas+AM&gmd%3Acontact-gmd%3ACI_ResponsibleParty-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aaddress-gmd%3ACI_Address-gmd%3ApostalCode-gco%3ACharacterString=78412&gmd%3Acontact-gmd%3ACI_ResponsibleParty-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aaddress-gmd%3ACI_Address-gmd%3Acountry-gco%3ACharacterString=USA&gmd%3Acontact-gmd%3ACI_ResponsibleParty-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aaddress-gmd%3ACI_Address-gmd%3AelectronicMailAddress-gco%3ACharacterString=michael.vandeneijnden%40tamucc.edu&gmd%3Acontact-gmd%3ACI_ResponsibleParty-gmd%3Arole-gmd%3ACI_RoleCode=CI_RoleCode_principalInvestigator&gmd%3AdateStamp-gco%3ADate=0%2F01%2F2012&gmd%3AmetadataStandardName-gco%3AcharacterString=ISO+19115-2+Geographic+Information+-+Metadata+-+Part+2%3A+Extensions+for+Imagery+and+Gridded+Data&gmd%3AmetadataStandardVersion-gco%3AcharacterString=ISO+19115-2%3A2009%28E%29&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Acitation-gmd%3ACI_Citation-gmd%3Atitle-gco%3ACharacterString=Test&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Acitation-gmd%3ACI_Citation-gmd%3Aalternatetitle-gco%3ACharacterString=Test+Title&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Acitation-gmd%3ACI_Citation-gmd%3Adate-gmd%3ACI_Date-gmd%3Adate-gco%3ADate=01%2F01%2F2013&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Acitation-gmd%3ACI_Citation-gmd%3Adate-gmd%3ACI_Date-gmd%3AdateType-CI_DateTypeCode=publication&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Aabstract-gco%3ACharacterString=This+box+is+TOO+small&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Astatus-gco%3ACharacterString=OK&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Apurpose-gco%3ACharacterString=none&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3ApointOfContact-gmd%3ACI_ResponsibleParty-gmd%3AindividualName-gco%3ACharacterString=The+Other+Guy&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3ApointOfContact-gmd%3ACI_ResponsibleParty-gmd%3AorganisationName-gco%3ACharacterString=GRIIDC&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3ApointOfContact-gmd%3ACI_ResponsibleParty-gmd%3ApositionName-gco%3ACharacterString=Uber+Dude&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3ApointOfContact-gmd%3ACI_ResponsibleParty-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aphone-gmd%3ACI_Telephone-gmd%3Avoice-gco%3ACharacterString=yes&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3ApointOfContact-gmd%3ACI_ResponsibleParty-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aphone-gmd%3ACI_Telephone-gmd%3Afacsimile-gco%3ACharacterString=no&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3ApointOfContact-gmd%3ACI_ResponsibleParty-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aaddress-gmd%3ACI_Address-gmd%3AdeliveryPoint-gco%3ACharacterString=here&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3ApointOfContact-gmd%3ACI_ResponsibleParty-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aaddress-gmd%3ACI_Address-gmd%3Acity-gco%3ACharacterString=there&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3ApointOfContact-gmd%3ACI_ResponsibleParty-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aaddress-gmd%3ACI_Address-gmd%3AadministrativeArea-gco%3ACharacterString=somewhere&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3ApointOfContact-gmd%3ACI_ResponsibleParty-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aaddress-gmd%3ACI_Address-gmd%3ApostalCode-gco%3ACharacterString=12345+TX&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3ApointOfContact-gmd%3ACI_ResponsibleParty-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aaddress-gmd%3ACI_Address-gmd%3Acountry-gco%3ACharacterString=USA&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3ApointOfContact-gmd%3ACI_ResponsibleParty-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aaddress-gmd%3ACI_Address-gmd%3AelectronicMailAddress-gco%3ACharacterString=me%40notme.net&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3ApointOfContact-gmd%3ACI_ResponsibleParty-gmd%3Arole-gmd%3ACI_RoleCode=CI_RoleCode_principalInvestigator&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3AdescriptiveKeywords_theme-gmd%3AdescriptiveKeywords-gmd%3AMD_Keywords=blaa%3Bfoo%3Bbar&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3AdescriptiveKeywords_theme-gmd%3AdescriptiveKeywords-gmd%3Atype-gmd%3AMD_KeywordTypeCode=theme&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3AdescriptiveKeywords_place-gmd%3AdescriptiveKeywords-gmd%3AMD_Keywords=there%3Beverywhere%3Bsomehwere&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3AdescriptiveKeywords_place-gmd%3AdescriptiveKeywords-gmd%3Atype-gmd%3AMD_KeywordTypeCode=place&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Aextent-gmd%3AEX_Extent-gmd%3Adescription-gco%3ACharacterString=again%21&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Aextent-gmd%3AEX_Extent-gmd%3AgeographicElement-gmd%3AextentTypeCode-gco%3ABoolean=1&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Aextent-gmd%3AEX_Extent-gmd%3AgeographicElement-gmd%3AwestBoundLongitude-gco%3ADecimal=20.1&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Aextent-gmd%3AEX_Extent-gmd%3AgeographicElement-gmd%3AeastBoundLongitude-gco%3ADecimal=90.1&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Aextent-gmd%3AEX_Extent-gmd%3AgeographicElement-gmd%3AsouthBoundLatitude-gco%3ADecimal=23.1&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Aextent-gmd%3AEX_Extent-gmd%3AgeographicElement-gmd%3AnorthBoundLatitude-gco%3ADecimal=94.4&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Aextent-gmd%3AEX_Extent-gmd%3AtemporalElement-gml%3ATimePeriod-gml%3Adescription-gco%3ACharacterString=time&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Aextent-gmd%3AEX_Extent-gmd%3AtemporalElement-gml%3ATimePeriod-gml%3AbeginPosition-gco%3Adate=now&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Aextent-gmd%3AEX_Extent-gmd%3AtemporalElement-gml%3ATimePeriod-gml%3AendPosition-gco%3Adate=end&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Aextent-gmd%3AEX_Extent-gmd%3AtemporalElement-gml%3ATimePeriod-gml%3Aduration-gco%3Adate=long&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Aextent-gmd%3AEX_Extent-gmd%3AtemporalElement-gml%3ATimePeriod-gml%3AtimeInterval-gco%3Afloat=1&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3AsupplementalInformation-gco%3ACharacterString=No%2C+we+are+all+cool+here%21


-->


