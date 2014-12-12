<?php
// Module: owsException.php
// Author(s): Michael van den Eijnden
// Last Updated: 3 August 2012
// Parameters: None
// Returns: xml OWS Exception
// Purpose: Will return a XML document with error, or no data returned.

require_once 'xmlBuilder.php';


//class exceptionBuilder
//{
	//public function __construct()
	//{
		function showException($code,$text,$locator=null)
		{
	
		$xmlBld = new xmlBuilder();
		
		$root = $xmlBld->createXmlNode($xmlBld->doc,'ExceptionReport');
		$xmlBld->addAttribute($root,'xmlns','http://www.opengis.net/ows/1.1');
		$xmlBld->addAttribute($root,'xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
		$xmlBld->addAttribute($root,'xsi:schemaLocation','http://www.opengis.net/ows/1.1 ../owsExceptionReport.xsd');
		$xmlBld->addAttribute($root,'version','1.1.0');
		$xmlBld->addAttribute($root,'xml:lang','en');
		$exceptionNode = $xmlBld->createXmlNode($root,'Exception');
	//}

	//public function addException($code,$text,$locator)
	//{
		$xmlBld->addAttribute($exceptionNode,'exceptionCode',$code);
		if (isset($locator))
		{
			$xmlBld->addAttribute($exceptionNode,'locator',$locator);
		}
		$xmlBld->addChildValue($exceptionNode,'ExceptionText',$text);
	//}

	//public function __tostring()
	//{
		return $xmlBld;
	//}
		}


?>