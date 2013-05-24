<?php

if (!file_exists('/var/tmp/gmxCodelists.xml')) {
    file_put_contents('/var/tmp/gmxCodelists.xml', fopen('http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml', 'r'));
}

$codelists = simplexml_load_file('/var/tmp/gmxCodelists.xml');

foreach ($codelists->codelistItem as $codelistItem) {
    $codelist = $codelistItem->CodeListDictionary->attributes('gml',true);
    $count = 1;
    foreach ($codelistItem->CodeListDictionary->codeEntry as $codeEntry) {
        $entry = $codeEntry->CodeDefinition->children('gml',true);
        $GLOBALS['CodeLists'][(string) $codelist->id][(string) $entry->identifier]['Name'] = (string) $entry->identifier;
        $GLOBALS['CodeLists'][(string) $codelist->id][(string) $entry->identifier]['DomainCode'] = sprintf('%03d',$count);
        $GLOBALS['CodeLists'][(string) $codelist->id][(string) $entry->identifier]['Definition'] = (string) $children->description;
        $count++;
    }
}

?>
