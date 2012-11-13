<?php
// Module: getTaskDetails.php
// Author(s): Michael van den Eijnden
// Last Updated: 15 August 2012
// Parameters: None
// Returns: xml rest request
// Purpose: Entry point in REST (using Slim) to return XML data

ini_set('display_errors',true);
error_reporting(-1);

require 'Slim/Slim.php';
require 'getTaskData.php';

$debug = true;

//With default settings
$app = new Slim();

$app->config('debug', $debug);

if (!$debug)
{
	//$app->error();
}

//Default function
$app->get('/', function () use ($app) {
    global $debug;
	$allGetParams = $app->request()->get();
	if (isset($allGetParams['recache'])) {
		unset($allGetParams['recache']);
		$results = getData($allGetParams,true); //Has to return TRUE or no XML.
	}
	else {
		$results = getData($allGetParams); //Has to return TRUE or no XML.
	}
	$response = $app->response();
	$response['Content-Type'] = 'application/xml';
	$response['X-Powered-By'] = 'Slim';
	//}
});

$app->run();

?>
