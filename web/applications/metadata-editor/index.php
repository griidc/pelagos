<?php
//Show Errors (debug only)
error_reporting(-1);
ini_set('display_errors', '1');

drupal_add_library('system', 'ui.datepicker');

drupal_add_js('/includes/jquery-validation/jquery.validate.js',array('type'=>'external'));

if (isset($_POST))

{

	if (count($_POST)>1)
	{
		
		
		include 'makeXML.php';
		
		makeXML($_POST);
		//exit;
	}
	
	//echo '<pre>';
	//var_dump($_POST);
	//echo '</pre>';
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

<!--
<style>
legend 
{
	text-shadow: 2px 2px 3px rgba(150, 150, 150, 0.75);
	font-family:Verdana, Geneva, sans-serif;
	font-size:1.4em;
	border-top: 1px solid #009;
	border-left: 1px solid #009;
	border-right:  2px solid #009;
	border-radius: 10px;
	-webkit-box-shadow: 4px 4px 5px rgba(50, 50, 50, 0.75);
	-moz-box-shadow: 4px 4px 5px rgba(50, 50, 50, 0.75);
	box-shadow: 4px 4px 5px rgba(50, 50, 50, 0.75);
	padding: 3px;
}

fieldset 
{
	border: 1px solid #009;
	-webkit-border-radius: 10px;
	-moz-border-radius: 10px;
	border-radius: 10px;
	-webkit-box-shadow: 4px 4px 5px rgba(50, 50, 50, 0.75);
	-moz-box-shadow: 4px 4px 5px rgba(50, 50, 50, 0.75);
	box-shadow: 4px 4px 5px rgba(50, 50, 50, 0.75);
}
</style>
 -->

<table width="40%,*">
<tr><td>
<fieldset>
<legend>Metadata</legend>
<form method="post">

<?php

	$htmlString;
	$jsString;
	$validateRules;
	$validateMessages;
	$qtipS;
	
	//echo "<script>\n";
	//echo $twig->render('js/base.js', array('jqueryString' => $jqueryString,'validateString' => $validateString));
	//echo "<script>\n";

	include 'MI_Metadata.php';
	$myMImeta = new MI_Metadata('MIMeta',uniqid());
	$myMImeta->getHTML();

	
	
	require_once '/usr/share/pear/Twig/Autoloader.php';
	Twig_Autoloader::register();
	
	$loader = new Twig_Loader_Filesystem('./templates');
	$twig = new Twig_Environment($loader);
	

	
?>

<input type="submit"/>
<input type="reset"/>

</form>
</fieldset>
</td>
<td></td></tr></table>



<!--

Test URL:

https://proteus.tamucc.edu/metadata/?gmd%3Afileidentifier-gco%3AcharacterString=1049804219802198049809421&gmd%3Alanguage-gco%3AcharacterString=eng%3B+USA&gmd%3AcharacterSet-gmd%3AMD_CharacterSetCode=utf8&gmd%3AhierarchyLevel-gmd%3AMD_ScopeCode=dataset&gmd%3Acontact-gmd%3ACI_ResponsibleParty%21contactPI-gmd%3AindividualName-gco%3ACharacterString=Michael+van+den+Eijnden&gmd%3Acontact-gmd%3ACI_ResponsibleParty%21contactPI-gmd%3AorganisationName-gco%3ACharacterString=Texas+AM&gmd%3Acontact-gmd%3ACI_ResponsibleParty%21contactPI-gmd%3ApositionName-gco%3ACharacterString=Dude&gmd%3Acontact-gmd%3ACI_ResponsibleParty%21contactPI-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aphone-gmd%3ACI_Telephone-gmd%3Avoice-gco%3ACharacterString=361-555-1212&gmd%3Acontact-gmd%3ACI_ResponsibleParty%21contactPI-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aphone-gmd%3ACI_Telephone-gmd%3Afacsimile-gco%3ACharacterString=361-989-4754&gmd%3Acontact-gmd%3ACI_ResponsibleParty%21contactPI-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aaddress-gmd%3ACI_Address-gmd%3AdeliveryPoint-gco%3ACharacterString=Harte&gmd%3Acontact-gmd%3ACI_ResponsibleParty%21contactPI-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aaddress-gmd%3ACI_Address-gmd%3Acity-gco%3ACharacterString=Corpus+Christi&gmd%3Acontact-gmd%3ACI_ResponsibleParty%21contactPI-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aaddress-gmd%3ACI_Address-gmd%3AadministrativeArea-gco%3ACharacterString=Corpus&gmd%3Acontact-gmd%3ACI_ResponsibleParty%21contactPI-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aaddress-gmd%3ACI_Address-gmd%3ApostalCode-gco%3ACharacterString=78412&gmd%3Acontact-gmd%3ACI_ResponsibleParty%21contactPI-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aaddress-gmd%3ACI_Address-gmd%3Acountry-gco%3ACharacterString=USA&gmd%3Acontact-gmd%3ACI_ResponsibleParty%21contactPI-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aaddress-gmd%3ACI_Address-gmd%3AelectronicMailAddress-gco%3ACharacterString=me%40mail.net&gmd%3Acontact-gmd%3ACI_ResponsibleParty%21contactPI-gmd%3Arole-gmd%3ACI_RoleCode=CI_RoleCode_principalInvestigator&gmd%3AdateStamp-gco%3ADate=01%2F01%2F2002&gmd%3AmetadataStandardName-gco%3AcharacterString=ISO+19115-2+Geographic+Information+-+Metadata+-+Part+2%3A+Extensions+for+Imagery+and+Gridded+Data&gmd%3AmetadataStandardVersion-gco%3AcharacterString=ISO+19115-2%3A2009%28E%29&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Acitation-gmd%3ACI_Citation-gmd%3Atitle-gco%3ACharacterString=Title&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Acitation-gmd%3ACI_Citation-gmd%3Aalternatetitle-gco%3ACharacterString=Title+2&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Acitation-gmd%3ACI_Citation-gmd%3Adate-gmd%3ACI_Date-gmd%3Adate-gco%3ADate=01%2F02%2F2013&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Acitation-gmd%3ACI_Citation-gmd%3Adate-gmd%3ACI_Date-gmd%3AdateType-CI_DateTypeCode=publication&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Aabstract-gco%3ACharacterString=Blaaa&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Astatus-gco%3ACharacterString=Good&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Apurpose-gco%3ACharacterString=Because&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3ApointOfContact-gmd%3ACI_ResponsibleParty%21contactDI-gmd%3AindividualName-gco%3ACharacterString=Other+Person&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3ApointOfContact-gmd%3ACI_ResponsibleParty%21contactDI-gmd%3AorganisationName-gco%3ACharacterString=That+one&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3ApointOfContact-gmd%3ACI_ResponsibleParty%21contactDI-gmd%3ApositionName-gco%3ACharacterString=Head+Guy&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3ApointOfContact-gmd%3ACI_ResponsibleParty%21contactDI-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aphone-gmd%3ACI_Telephone-gmd%3Avoice-gco%3ACharacterString=212-557-1644&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3ApointOfContact-gmd%3ACI_ResponsibleParty%21contactDI-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aphone-gmd%3ACI_Telephone-gmd%3Afacsimile-gco%3ACharacterString=556-854-6521&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3ApointOfContact-gmd%3ACI_ResponsibleParty%21contactDI-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aaddress-gmd%3ACI_Address-gmd%3AdeliveryPoint-gco%3ACharacterString=There&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3ApointOfContact-gmd%3ACI_ResponsibleParty%21contactDI-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aaddress-gmd%3ACI_Address-gmd%3Acity-gco%3ACharacterString=Place&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3ApointOfContact-gmd%3ACI_ResponsibleParty%21contactDI-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aaddress-gmd%3ACI_Address-gmd%3AadministrativeArea-gco%3ACharacterString=Somewhere&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3ApointOfContact-gmd%3ACI_ResponsibleParty%21contactDI-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aaddress-gmd%3ACI_Address-gmd%3ApostalCode-gco%3ACharacterString=Something&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3ApointOfContact-gmd%3ACI_ResponsibleParty%21contactDI-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aaddress-gmd%3ACI_Address-gmd%3Acountry-gco%3ACharacterString=Dunno&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3ApointOfContact-gmd%3ACI_ResponsibleParty%21contactDI-gmd%3AcontactInfo-gmd%3ACI_Contact-gmd%3Aaddress-gmd%3ACI_Address-gmd%3AelectronicMailAddress-gco%3ACharacterString=Whatever&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3ApointOfContact-gmd%3ACI_ResponsibleParty%21contactDI-gmd%3Arole-gmd%3ACI_RoleCode=CI_RoleCode_principalInvestigator&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3AdescriptiveKeywords_theme-gmd%3AdescriptiveKeywords-gmd%3AMD_Keywords=word1%3Btest%3Bblaaa%3Bnothing&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3AdescriptiveKeywords_theme-gmd%3AdescriptiveKeywords-gmd%3Atype-gmd%3AMD_KeywordTypeCode=theme&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3AdescriptiveKeywords_place-gmd%3AdescriptiveKeywords-gmd%3AMD_Keywords=uhh%3Bi+guess&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3AdescriptiveKeywords_place-gmd%3AdescriptiveKeywords-gmd%3Atype-gmd%3AMD_KeywordTypeCode=place&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Aextent-gmd%3AEX_Extent-gmd%3Adescription-gco%3ACharacterString=Thing&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Aextent-gmd%3AEX_Extent-gmd%3AgeographicElement-gmd%3AextentTypeCode-gco%3ABoolean=1&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Aextent-gmd%3AEX_Extent-gmd%3AgeographicElement-gmd%3AwestBoundLongitude-gco%3ADecimal=20&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Aextent-gmd%3AEX_Extent-gmd%3AgeographicElement-gmd%3AeastBoundLongitude-gco%3ADecimal=20&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Aextent-gmd%3AEX_Extent-gmd%3AgeographicElement-gmd%3AsouthBoundLatitude-gco%3ADecimal=40&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Aextent-gmd%3AEX_Extent-gmd%3AgeographicElement-gmd%3AnorthBoundLatitude-gco%3ADecimal=50&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Aextent-gmd%3AEX_Extent-gmd%3AtemporalElement-gml%3ATimePeriod-gml%3Adescription-gco%3ACharacterString=Time&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Aextent-gmd%3AEX_Extent-gmd%3AtemporalElement-gml%3ATimePeriod-gml%3AbeginPosition-gco%3Adate=now&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Aextent-gmd%3AEX_Extent-gmd%3AtemporalElement-gml%3ATimePeriod-gml%3AendPosition-gco%3Adate=then&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Aextent-gmd%3AEX_Extent-gmd%3AtemporalElement-gml%3ATimePeriod-gml%3Aduration-gco%3Adate=after&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3Aextent-gmd%3AEX_Extent-gmd%3AtemporalElement-gml%3ATimePeriod-gml%3AtimeInterval-gco%3Afloat=later&gmd%3AidentificationInfo-gmd%3AMD_DataIdentification-gmd%3AsupplementalInformation-gco%3ACharacterString=No%2C+we+are+all+cool+here%21


-->


