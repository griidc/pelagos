<?php

include_once 'CI_ResponsibleParty.php';


echo <<<MI1

<label for="MI1">fileidentifier</label>
<input type="text" id="MI1" name="gmd:fileidentifier-gco:characterString"/><br/>

<label for="MI2">language</label>
<input type="text" id="MI2" name="gmd:language-gco:characterString"/ value="eng; USA"><br/>

<label for="MI3">characterSet</label>
<input type="text" id="MI3" name="gmd:characterSet-gmd:MD_CharacterSetCode" value="utf8"/><br/>

<label for="MI4">hierarchyLevel</label>
<input type="text" id="MI4" name="gmd:hierarchyLevel-gmd:MD_ScopeCode" value="dataset"/><br/>

MI1;

$mypi = new CI_ResponsibleParty('gmd:contact','contactPI',false,'CI_RoleCode_principalInvestigator');

echo <<<MI2

<label for="MI5">*dateStamp</label>
<input type="text" id="MI5" name="gmd:dateStamp-gco:Date"/><br/>

<label for="MI6">metadataStandardName</label>
<input type="text" id="MI6" name="gmd:metadataStandardName-gco:characterString" value="ISO 19115-2 Geographic Information - Metadata - Part 2: Extensions for Imagery and Gridded Data"/><br/>

<label for="MI7">metadataStandardVersion</label>
<input type="text" id="MI7" name="gmd:metadataStandardVersion-gco:characterString" value="ISO 19115-2:2009(E)"/><br/>

<!--label for="MI8">dataSetURI</label>
<input type="text" id="MI8" name="gmd:dataSetURI"/><br/-->
<hr>
MI2;

include_once 'MD_DataIdentifcation.php';
$mydi = new MD_DataIdentification('gmd:identificationInfo','DataIdent');

?>

