<?php

require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$debug = true;

//With default settings
$app = new \Slim\Slim();

$app->config('debug', $debug);

if (!$debug) {
	$app->error();
}

function getDOICitationJSON($doi)
{
    if ($doi == "") {
        $error = array();
        $error["doi"] = $doi;
        $error["statuscode"] = 404;
        $error["statusmessage"] = 'No DOI given.';
        $error["usage"] = './citation/doi/10.1234/4567';
        http_response_code(404);
        return json_encode($error,JSON_UNESCAPED_SLASHES);
    }

    $statusCodes = array();
    $statusCodes[200] = "The request was OK.";
    $statusCodes[204] = "The request was OK but there was no metadata available.";
    $statusCodes[404] = "The DOI requested doesn't exist.";
    $statusCodes[406] = "Can't serve any requested content type.";
      	
    $ch = curl_init();
    $url = 'http://dx.doi.org/'.$doi;
    $header = array('Accept: text/bibliography; style=apa; locale=utf-8');
    
    curl_setopt($ch, CURLOPT_URL, $url);
    // Since the request 303's (forwards) to http://data.crossref.org/, we have to turn follow on.
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt ($ch, CURLOPT_HEADER, false);
    curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    $curlinfo = curl_getinfo($ch);
    $httpstatus = $curlinfo["http_code"];
    curl_close($ch);
    
    if ($httpstatus == 200) {
        http_response_code($httpstatus);
        $citation = array();
        $citation["doi"] = $doi;
        $citation["citation"] = $output;
        $citation["style"] = 'apa';
        $citation["locale"] = 'utf-8';
        return json_encode($citation,JSON_UNESCAPED_SLASHES);
    }
    else
    {
        http_response_code($httpstatus);
        $error = array();
        $error["doi"] = $doi;
        $error["statuscode"] = $httpstatus;
        if (array_key_exists($httpstatus,$statusCodes)) {
            $error["statusmessage"] = $statusCodes[$httpstatus];
        }
        return json_encode($error,JSON_UNESCAPED_SLASHES);
    }
}

$app->get('/', function () use ($app) {
    global $debug;
    
    $allGetParams = $app->request()->get();
    
    if (isset($allGetParams["doi"])) {
        $doi = $allGetParams["doi"];
    }
    
    header('Content-Type:application/json');
    print getDOICitationJSON($doi);
    
    exit;
});

$app->get('/doi(/(:shoulder(/:body)))', function ($shoulder="",$body="") use ($app) {
    global $debug;
    
    $allGetParams = $app->request()->get();
    
    $doi = "$shoulder";
    if ($body != "") {
        $doi .= "/$body";
    }
    
    if (isset($allGetParams["doi"])) {
        $doi = $allGetParams["doi"];
    }
    
    header('Content-Type:application/json');
    print getDOICitationJSON($doi);
    
    exit;
});

$app->run();
