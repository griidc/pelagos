<?php
// @codingStandardsIgnoreFile
function getDOIMetaData($doi)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://ezid.cdlib.org/id/$doi");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    print curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
    print $output . "\n";
    curl_close($ch);
}

function createDOI($input)
{
    include 'doiConfig.php';
    utf8_encode($input);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://ezid.cdlib.org/shoulder/$doishoulder");
    curl_setopt($ch, CURLOPT_USERPWD, "$doiusername:$doipassword");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER,
    array('Content-Type: text/plain; charset=UTF-8','Content-Length: ' . strlen($input)));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output =  curl_exec($ch) . " message:" .curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return $output;
    curl_close($ch);
}

?>