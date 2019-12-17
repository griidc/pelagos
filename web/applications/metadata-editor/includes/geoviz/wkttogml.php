<?php
// @codingStandardsIgnoreFile
#Debug Only
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

$GLOBALS['pelagos_config']  = parse_ini_file('/etc/opt/pelagos.ini',true);
include_once $GLOBALS['pelagos_config']['paths']['share'].'/php/pdo.php';

function addGMLID($gml)
{
    
    $doc = new DomDocument('1.0','UTF-8');
    $doc->loadXML($gml,LIBXML_NOERROR);
    
    
    foreach ($doc->childNodes as $node)
    {
        $topNode = $node->nodeName;
        switch ($topNode)
        {
            case 'gml:Polygon':
            $node->setAttribute('gml:id','Polygon1');
            break;
            case 'gml:Curve':
            $node->setAttribute('gml:id','Curve1');
            break;
            case 'gml:Point':
            $node->setAttribute('gml:id','Point1');
            break;
            case 'gml:MultiPoint':
            $node->setAttribute('gml:id','Multipoint1');
            $i=0;
            foreach ($node->childNodes as $child)
            {
                $i++;
                $child->firstChild->setAttribute('gml:id',"Point$i");
            }
            break;
            case 'gml:MultiCurve':
            $node->setAttribute('gml:id','MultiCurve1');
            $i=0;
            foreach ($node->childNodes as $child)
            {
                $i++;
                $child->firstChild->setAttribute('gml:id',"Curve$i");
            }
            break;
            case 'gml:MultiSurface':
            $node->setAttribute('gml:id','MultiSurface');
            $i=0;
            foreach ($node->childNodes as $child)
            {
                $i++;
                $child->firstChild->setAttribute('gml:id',"Polygon$i");
            }
            break;
        }
    }
    
    $gml = $doc->saveXML();
    $cleanXML = new SimpleXMLElement($gml,LIBXML_NOERROR);
    $dom = dom_import_simplexml($cleanXML);
    $gml = $dom->ownerDocument->saveXML($dom->ownerDocument->documentElement);
    return $gml;
    
}

$wkt='';
$gml='';

if (isset($_POST["wkt"]))
{
	$wkt = $_POST["wkt"];
}

$configini = parse_ini_file($GLOBALS['pelagos_config']['paths']['conf'].'/db.ini',true);
$config = $configini["GOMRI_RW"];

$dbconnstr = 'pgsql:host='. $config["host"];
$dbconnstr .= ' port=' . $config["port"];
$dbconnstr .= ' dbname=' . $config["dbname"];
$dbconnstr .= ' user=' . $config["username"];
$dbconnstr .= ' password=' . $config["password"];

$conn = pdoDBConnect($dbconnstr);

$query = "SELECT ST_asGML(3,ST_GeomFromText('$wkt',4326),5,17)";

$row = pdoDBQuery($conn,$query);

$gml = $row[0]["st_asgml"];

//echo "{\"featureid\":\"$featureid\",\"reason\":\"$reason\"}";

echo addGMLID($gml);;

?>

