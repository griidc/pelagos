<?php
// @codingStandardsIgnoreFile
// Module: xmlBuilder.php
// Author(s): Michael van den Eijnden
// Last Updated: 16 August 2012
// Parameters: None
// Returns: a Class
// Purpose: An class with shortcuts for easy xml Dom document generation

class xmlBuilder
{
	public $doc;
		
	public function __construct()
	{
		// Start XML file
		$this->doc = new DomDocument('1.0','UTF-8');
	}
	
 	public function rowToXmlChild($parent,$row)
	{
		
		foreach ($row as $fieldname => $fieldvalue) {
			if (preg_match('/^__.+__(.+)/',$fieldname,$matches))
			{
				$child->setAttribute($matches[1],$fieldvalue);
			}
			else
			{
				$fieldvalue = utf8_encode($fieldvalue);
				$fieldvalue = html_entity_decode($fieldvalue, ENT_QUOTES,'UTF-8') ;
				$fieldvalue = strip_tags($fieldvalue);
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
	
	public function save($filename)
	{
		$this->doc->normalizeDocument();
		return $this->doc->save($filename);
	}
    
    public function __tostring()
	{
		$this->doc->normalizeDocument();
		return $this->doc->saveXML();
	}
}

?>
