<?php

class metaData
{
	public $htmlString;
	public $jsString;
	public $validateRules;
	public $validateMessages;
	public $jqUIs;
	public $qtipS;
	public $xmlArray;
	public $xmldoc;
	
	public $twig;
	
	private $loader;
	
	public function __construct()
	{
		require_once '/usr/share/pear/Twig/Autoloader.php';
		Twig_Autoloader::register();
		
		$this->loader = new Twig_Loader_Filesystem('./templates');
		$this->twig = new Twig_Environment($this->loader,array('autoescape' => false));
	}
	
	public function loadINI($filename)
	{
		#todo: Should support default values, and overwrite with instance values (use: array_merge)
		$ini_path = "config/$filename";
		return parse_ini_file($ini_path,true);
	}
	
	public function returnPath($path)
	{
		if (is_null($this->xmldoc))
		{
			return false;
		}
		
		//$xpath = "/gmi:MI_Metadata";
		$xpath = "/";
		
		$xpathdoc = new DOMXpath($this->xmldoc);
		
		$nodelevels = preg_split("/-/",$path);
		
		foreach ($nodelevels as $nodelevel)
		{
			$splitnodelevel = preg_split("/\!/",$nodelevel);
			
			$xpath .= "/" . $splitnodelevel[0];
		}
		
		//echo "$xpath<br>";
		
		$elements = $xpathdoc->query($xpath);
		
		$xmlArray = array();
		
		if (!is_null($elements)) {
			foreach ($elements as $element) {
				//echo "<br/>[". $element->nodeName. "]";
				
				$nodes = $element->childNodes;
				foreach ($nodes as $node) 
				{
					switch ($node->nodeType) 
					{
						
						case XML_TEXT_NODE:
						//$xmlArray[] = trim($node->textContent);
						break;
						
						case XML_ELEMENT_NODE:
						
						array_push($xmlArray, domnode_to_array($node));							
						//echo $node->nodeName. ":";
						//echo $node->nodeValue. "<br/>";
						break;
					}	
				}
			}
		}
		
		//$xmlArray = domnode_to_array($element->childNodes);
		
		if (count($xmlArray) > 0)
		{
			return $xmlArray;
		}
		else
		{
			return false;
		}
	}
}

?>