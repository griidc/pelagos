<?php
//ini_set('display_errors',true);
//error_reporting(0);

function getDOIMetaData($doi)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://n2t.net/ezid/id/$doi");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    print curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
    print $output . "\n";
    curl_close($ch);
}

function createDOI($input)
{
    //$input = '_target:http://www.google.com _coowners:apitest dc.creator:Michael van den Eijnden dc.title: Test dc.publisher: Harte dc.date: 01/01/2012';
    //$input = '_target: http://www.google.org/';
    utf8_encode($input);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://n2t.net/ezid/shoulder/doi:10.5072/FK2');
    curl_setopt($ch, CURLOPT_USERPWD, 'apitest:apitest');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER,
    array('Content-Type: text/plain; charset=UTF-8','Content-Length: ' . strlen($input)));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output =  curl_exec($ch) . " message:" .curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //print curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
    return $output;
    curl_close($ch);
}

?>