<?php
// Module: xmlBuilder.php
// Author(s): Michael van den Eijnden
// Last Updated: 3 August 2012
// Parameters: None
// Returns: a Class
// Purpose: An class to with shortcuts for easy xml Dom document generation

class xmlBuilder
{
	public $doc;
		
	public function __construct()
	{
		// Start XML file
		$this->doc = new DomDocument('1.0');
	}
	
 	public function rowToXmlChild($parent,$row)
	{
		
		foreach ($row as $fieldname => $fieldvalue) {
			if (substr($fieldname,0,8) == '__Attr__')
			{
				$child->setAttribute(substr($fieldname,8),$fieldvalue);
			}
			else
			{
				$fieldvalue = utf8_encode ($fieldvalue);
				$child = $this->doc->createElement($fieldname);
				$child = $parent->appendChild($child);
				$value = $this->doc->createTextNode($fieldvalue);
				$value = $child->appendChild($value);
			}
		} 
	} 
	
	public function addChildValue($parent,$fieldname,$fieldvalue)
	{
		$child = $this->doc->createElement($fieldname);
		$child = $parent->appendChild($child);
		$value = $this->doc->createTextNode($fieldvalue);
		$value = $child->appendChild($value);
		return $child;
	}
	
	public function addAttribute($node,$key,$value)
	{
		$node->setAttribute($key,$value);
	}
		
	public function createXmlNode($parent,$nodeName)
	{
		$node = $this->doc->createElement($nodeName);
		$node = $parent->appendChild($node);
		return $node;
	}
	
	public function __tostring()
	{
		//$this->doc->normalizeDocument();
		return $this->doc->saveXML();
	}
}

?>