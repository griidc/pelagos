<?php

header("Content-type: text/xml; charset=utf-8"); 

/*
echo <<<HEAD
<gmi:MI_Metadata xmlns="http://www.isotc211.org/2005/gmi"
xmlns:gco="http://www.isotc211.org/2005/gco"
xmlns:gmd="http://www.isotc211.org/2005/gmd"
xmlns:gmi="http://www.isotc211.org/2005/gmi"
xmlns:gml="http://www.opengis.net/gml/3.2"
xmlns:gmx="http://www.isotc211.org/2005/gmx"
xmlns:gsr="http://www.isotc211.org/2005/gsr"
xmlns:gss="http://www.isotc211.org/2005/gss"
xmlns:gts="http://www.isotc211.org/2005/gts"
xmlns:xlink="http://www.w3.org/1999/xlink"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:schemaLocation="http://www.isotc211.org/2005/gmi http://www.ngdc.noaa.gov/metadata/published/xsd/schema.xsd">

HEAD;
//*/

$lastarr = array();

$tablevel = 0;

function makeXML($xml)
{
		//array_walk($xml,'createNodes');
		
		array_shift($xml); #To get rid of the Q from Drupal
		
		createNodesXML($xml);
}

function createXmlNode($doc,$parent,$nodeName)
{
	//echo $nodeName;
	$node = $doc->createElement($nodeName);
	$node = $parent->appendChild($node);
	return $node;
}

function addXMLChildValue($doc,$parent,$fieldname,$fieldvalue)
{
	$child = $doc->createElement($fieldname);
	$child = $parent->appendChild($child);
	$value = $doc->createTextNode($fieldvalue);
	$value = $child->appendChild($value);
	return $child;
}

function createNodesXML($xml)
{
	$doc = new DomDocument('1.0','UTF-8');
	
	$root = createXmlNode($doc,$doc,'gmi:MI_Metadata');
	
	$root->setAttribute('xmlns','http://www.isotc211.org/2005/gmi');
	$root->setAttribute('xmlns:gco','http://www.isotc211.org/2005/gco');
	$root->setAttribute('xmlns:gmd','http://www.isotc211.org/2005/gmd');
	$root->setAttribute('xmlns:gmi','http://www.isotc211.org/2005/gmi');
	$root->setAttribute('xmlns:gml','http://www.isotc211.org/2005/gml');
	$root->setAttribute('xmlns:gmx','http://www.isotc211.org/2005/gmx');
	$root->setAttribute('xmlns:gsr','http://www.isotc211.org/2005/gsr');
	$root->setAttribute('xmlns:gss','http://www.isotc211.org/2005/gss');
	$root->setAttribute('xmlns:gts','http://www.isotc211.org/2005/gts');
	$root->setAttribute('xmlns:xlink','http://www.w3.org/1999/xlink');
	$root->setAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
	$root->setAttribute('xsi:schemaLocation','http://www.isotc211.org/2005/gmi http://www.ngdc.noaa.gov/metadata/published/xsd/schema.xsd');
	
	$parent = $root;
	
	foreach ($xml as $key=>$val)
	{
		$nodelevels = preg_split("/-/",$key);
		
		$nodecnt = count($nodelevels);
		
		$icnt=0;
		
		//var_dump($nodelevels);
		
		foreach ($nodelevels as $nodelevel)
		{
			$icnt++;
			if ($icnt == $nodecnt)
			{
				#makechild
				$nodename = str_replace(":","_",$nodelevels[$nodecnt-2]);
				$thisparent = ${'node'.$nodename};
				addXMLChildValue($doc,$thisparent,$nodelevel,$val);
			}
			else
			{
				#makenode
				$nodename = str_replace(":","_",$nodelevel);
				if (!isset(${'node'.$nodename}))
				{
					${'node'.$nodename} = createXmlNode($doc,$parent,$nodelevel);
				}
				$parent = ${'node'.$nodename};
			}
		}
		$parent = $root;
	}
	$doc->normalizeDocument();
	echo $doc->saveXML();
	
	
}

function closenodes($item,$key)
{
	global $tablevel;
	$tablevel--;
	sendtabs($tablevel);
	echo "</$item>\n";
}

function createnodesMAN($item,$key)
{
	global $lastarr;
	global $tablevel;
	
	$closearr = array();
	
	$nodehier = preg_split("/-/",$key);
	
	/*
	echo "now:";
	var_dump($nodehier);
	echo "\nlevel:";
	var_dump($lastarr);
	echo "\n";
	//*/
	
	
	$nodecnt = count($nodehier)-1;
	$lastcnt = count($lastarr)-1;
	
	

	for ($icnt=0;$icnt<=$lastcnt;$icnt++)
	{
		if ($icnt <= $nodecnt)
		{
			if ($lastarr[$icnt] != $nodehier[$icnt])
			{
				$closearr[] = $lastarr[$icnt];
				$lastarr = removeFromArray($lastarr,$icnt);
				//$lastcnt = count($lastarr)-1;
			}
		}
		else
		{
			//$closearr[] = $lastarr[$icnt];
		}
	}
	
	//array_reverse($closearr);
	
	/*
	echo "\nclose:";
	var_dump($closearr);
	//*/
	
	array_walk($closearr,'closenodes');
	
	$nodecnt = count($nodehier)-1;
	$lastcnt = count($lastarr)-1;
	
		
	for ($icnt=0;$icnt<=$nodecnt;$icnt++)
	{
		if ($icnt==$nodecnt)
		{
			sendtabs($tablevel);
			makechild($item,$nodehier[$icnt]);
		}
		else
		{
			//echo "L=$lastcnt\nI=$icnt\n";
			
			if ($icnt <= $lastcnt)
			{
				if ($lastarr[$icnt] != $nodehier[$icnt])
				{
					if ($lastarr[$icnt] <> "")
					{
						
						//sendtabs($tablevel);
						//echo "</$lastarr[$icnt]>\n";
						//$lastarr[$icnt] = $nodehier[$icnt];
						//$lastarr = removeFromArray($lastarr,$icnt);
						//$tablevel--;
						sendtabs($tablevel);
						echo "<$nodehier[$icnt]>\n";
						$tablevel++;
						//$lastarr[] = $nodehier[$icnt];
						$lastarr = insertIntoArray($lastarr,$icnt,$nodehier[$icnt]);
				}
				else
				{
					//$tablevel++;
					//echo "<$nodehier[$icnt]>\n";					
				}
				}
			}
			else
			{
				{
					if ($icnt <= $nodecnt)
					{
						sendtabs($tablevel);
						echo "<$nodehier[$icnt]>\n";
						$tablevel++;
						$lastarr[] = $nodehier[$icnt];
					}
				}
			}
		}
		
	}
	
	//$lastarr = $nodehier;
	
}

function sendtabs($level)
{
	for ($i=0;$i<=$level;$i++)
	{
		echo "  ";
	}
}

function makechild($item,$key)
{
	echo "<$key>$item</$key>\n";
	
}


function createnodesALT($item,$key)
{
	
	$nodehier = preg_split("/-/",$key);
	
	echo '<pre>';
	var_dump($nodehier);
	echo '</pre>';
	
		
	$icnt = 0;
	
	foreach ($nodehier as $onode)
	{
			$icnt++;
		for ($i=1;$i<$icnt;$i++)
		{
			echo "\t";
		}
			
			if ($icnt==count($nodehier))
			{
				
				echo "<$onode>";	
			}
			else
			{
				
				echo "<$onode>\n";
			}
	
	}
	
	//var_dump ($temparr);
	//echo '<br>';
	
	$trimmed = trim($item);
	
	echo "$trimmed";
	
	$revarr = array_reverse($nodehier);
	
	$icnt = count($revarr);
	
	foreach ($revarr as $cnode)
	{
		$icnt--;
		
		if ($icnt==count($revarr))
		{
			echo "</$cnode>";	
		}
		else
		{
			for ($i=1;$i<$icnt;$i++)
			{
				echo "\t";
			}
			echo "</$cnode>\n";
		}
	}
}

function removeFromArray($array,$position)
{
	$temparr = array();
	
	for ($i=0;$i<=count($array)-1;$i++)
	{
		if ($i<>$position)
		{
			$temparr[] = $array[$i];
		}
	}
	
	return $temparr;
}

function insertIntoArray($array,$position,$var)
{
	$temparr = array();
	
	$i = 0;
	
	if ($position>count($array))
	{
		$temparr = $array;
		array_push($temparr,$var);
	}
	else
	{
		
		foreach ($array as $node)
		{
			if ($i==$position)
			{
				$temparr[] = $var;
				
			}
			else
			{
				$temparr[] = $node;
			}
			$i++;
		}
	}
	return $temparr;
}

?>