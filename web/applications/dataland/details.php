<?php include_once '/usr/local/share/GRIIDC/php/aliasIncludes.php';
error_reporting(E_ALL);
ini_set("display_errors", 1);

include_once '/usr/local/share/GRIIDC/php/pdo.php';

require_once '/usr/share/pear/Twig/Autoloader.php';
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem('./templates');
$twig = new Twig_Environment($loader,array('autoescape' => false));

$udi='';
$dsscript='';
$prow ='';
$mrow ='';
$mprow ='';

$URI = preg_split('/\?/',$_SERVER['REQUEST_URI']);

$URIs = preg_split('/\//',$_SERVER['REQUEST_URI']);

$udi = $URIs[2];

if ($udi <> '')
{
	$configini = parse_ini_file("/etc/griidc/db.ini",true);
	$pconfig = $configini["GOMRI_RW"];

	$mconfig = $configini["RIS_RO"];

	$dbconnstr = 'pgsql:host='. $pconfig["host"];
	$dbconnstr .= ' port=' . $pconfig["port"];
	$dbconnstr .= ' dbname=' . $pconfig["dbname"];
	$dbconnstr .= ' user=' . $pconfig["username"];
	$dbconnstr .= ' password=' . $pconfig["password"];

	$pconn = pdoDBConnect($dbconnstr);

	$pquery = "
	SELECT * , ST_AsText(metadata.geom) AS \"the_geom\"
	FROM registry
	LEFT OUTER JOIN datasets ON substr(registry.registry_id,0,17) = datasets.dataset_udi
	LEFT OUTER JOIN metadata on registry.registry_id = metadata.registry_id
	WHERE registry.registry_id LIKE '$udi%'
	ORDER BY registry.registry_id DESC
	LIMIT 1
	;
	";

	$prow = pdoDBQuery($pconn,$pquery);
	//echo "<pre>";
	//var_dump($prow);

	//echo "</pre>";

	if ($prow["the_geom"] == null OR $prow == null)
	{
		if ($prow == null)
		{
			$dsscript = "addImage('/data/includes/images/nodata.png',0.4);";
		}
		else
		{
			$dsscript = "addImage('/data/includes/images/labonly.png',0.4);";
		}
	}
	else
	{
		$dsscript = 'addFeatureFromWKT("'. $prow['the_geom'] .'",{"udi":"'.$prow['dataset_udi'].'"});gotoAllFeatures();';
	}

	$dbconnstr = 'mysql:host='. $mconfig["host"];
	$dbconnstr .= ';port=' . $mconfig["port"];
	$dbconnstr .= ';dbname=' . $mconfig["dbname"];
	$mconn = new PDO($dbconnstr,
		$mconfig["username"],
		$mconfig["password"],
		array(PDO::ATTR_PERSISTENT => true));

	//$mconn = pdoDBConnect($dbconnstr);

	$mquery = "
	SELECT * FROM Projects
	JOIN Programs ON Projects.Program_ID = Programs.Program_ID
	LEFT OUTER JOIN FundingSource ON  FundingSource.Fund_ID = Programs.Program_FundSrc
	WHERE Programs.Program_ID = '".$prow["project_id"]."'
	AND Projects.Project_ID = '".$prow["task_uid"]."'
	;
	";

	$mrow = pdoDBQuery($mconn,$mquery);

	$mquery = "
	SELECT * FROM People
	LEFT OUTER JOIN Institutions ON Institutions.Institution_ID = People.People_Institution
	LEFT OUTER JOIN Departments ON Departments.Department_ID = People.People_Department
	WHERE People_ID = ".$prow["primary_poc"]."
	;
	";

	//echo $mquery;

	$mprow = pdoDBQuery($mconn,$mquery);

	//echo "<pre>";
	//var_dump($mprow);
	//echo "</pre>";
}
function transform($xml, $xsl) { 
	if ($xml <> "" AND $xml != null)
	{
	
		$xml_doc = new DOMDocument();
		$xml_doc->loadXML($xml);
		
		// XSL
		$xsl_doc = new DOMDocument();
		$xsl_doc->load($xsl);
		
		// Proc
		$proc = new XSLTProcessor();
		$proc->importStylesheet($xsl_doc);
		$newdom = $proc->transformToDoc($xml_doc);
		
		return $newdom->saveXML();
	}
	else
	{
		return "No Metadata Available";
	}
}

?>

<script type="text/javascript" src="//code.jquery.com/jquery-1.9.1.js"></script>
<script type="text/javascript" src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
<link type="text/css" rel="stylesheet" href="/data/includes/css/details.css" type="text/css">
<script type="text/javascript" src="/includes/openlayers/lib/OpenLayers.js"></script>
<script type="text/javascript" src="//maps.google.com/maps/api/js?v=3&sensor=false"></script>

<script src="/includes/geoviz/geoviz.js"></script>

<style>

</style>

<script>
	$(function() {
	$( "#tabs" ).tabs();
	});
  
	$(document).ready(function() 
	{
		
		initMap('olmap',{'onlyOneFeature':false,'allowModify':false,'allowDelete':false,'staticMap':true});
  
		$("#downloadds").click(function() {
			window.location = 'https://data.gulfresearchinitiative.org/data-discovery?filter=<?php echo $udi;?>';
		});
		
		$("#metadatadl").click(function() {
			window.location = 'https://data.gulfresearchinitiative.org/metadata/<?php echo $udi;?>';
		});
		
	});
	
	$(document).on('imready', function(e) {
		<?php echo $dsscript;?>
		console.log("added");
	});
  
</script>
  
<table border="1" width="100%">
<tr>
<td width="30%">

<div id="olmap" style="width: 640px;height: 480px;"></div>

</td>
<td style="padding:10px;" width="70%" valign="top">
<?php echo $twig->render('summary.html', array('pdata' => $prow,'mdata' => $mrow,'mpdata' => $mprow)); ?>
</td>
</tr>

<tr>
<td colspan="2">
	<div id="tabs">
		<ul>
			<li><a href="#tabs-1">Details</a></li>
			<li><a href="#tabs-2">Metadata</a></li>
			<!--
			<li><a href="#tabs-3">Publications</a></li>
			<li><a href="#tabs-4">Manifest</a></li>
			-->
		</ul>
		<div class="tabb" id="tabs-1">
			<?php echo $twig->render('details.html', array('pdata' => $prow,'mdata' => $mrow,'mpdata' => $mprow)); ?>
		</div>
		<div class="tabb" id="tabs-2">
		<div>
			<?php 
			//$xml = file_get_contents("/sftp/data/$udi/$udi.met");
			//$xsl = file_get_contents('/home/users/mvandeneijnden/public_html/mapmockup/xsl/xml-to-html-ISO.xsl');
			
			//$xml = "/sftp/data/$udi/$udi.met";
			$xml = '';
			$xsl = 'xsl/xml-to-html-ISO.xsl';
			
			if ($prow <>'')
			{
				$xml = $prow["metadata_xml"];
			}
			
			echo transform($xml,$xsl);
			
			//echo $xml;
						
			?>
			</div>
		</div>
		<!--
		<div class="tabb" id="tabs-3">
			<?php //include "pubs.html"; ?>
		</div>
		<div class="tabb" id="tabs-4">
			<?php //include "manifest.html"; ?>
		</div>
		-->
	</div>
</td>
</tr>
</table>