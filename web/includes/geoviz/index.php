<title>GeoViz Template</title>
<link type="text/css" rel="stylesheet" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
<script type="text/javascript" src="//code.jquery.com/jquery-1.9.1.js"></script>
<script type="text/javascript" src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<script type="text/javascript" src="/includes/openlayers/lib/OpenLayers.js"></script>
<script type="text/javascript" src="//maps.google.com/maps/api/js?v=3&sensor=false"></script>

<script src="geoviz.js"></script>

<?php

	//var_dump($_GET);

	error_reporting(E_ALL);
	ini_set("display_errors", 1);

	include_once '/usr/local/share/GRIIDC/php/pdo.php';
	
	$configini = parse_ini_file("/etc/griidc/db.ini",true);
	$config = $configini["GOMRI_RW"];

	$dbconnstr = 'pgsql:host='. $config["host"];
	$dbconnstr .= ' port=' . $config["port"];
	$dbconnstr .= ' dbname=' . $config["dbname"];
	$dbconnstr .= ' user=' . $config["username"];
	$dbconnstr .= ' password=' . $config["password"];

	$conn = pdoDBConnect($dbconnstr);
	
	//$query = 'SELECT "udi", "title", "poi1", "poi2", ST_AsText(the_geom) as "the_geom" FROM "public"."datasets_test";';
	$query = '
	SELECT registry."registry_id" AS "udi", datasets.project_id AS "pid", registry."dataset_title" as "title", registry."dataset_poc_name", ST_AsText(metadata.geom) as "the_geom" FROM "public"."registry" 
	LEFT OUTER JOIN datasets ON substr(registry.registry_id,0,17) = datasets.dataset_udi
	LEFT OUTER JOIN metadata ON registry."registry_id" = metadata."registry_id"
	WHERE metadata.geom <> \'\'';
	
	if (isset($_GET["project_id"]))
	{
		$query .= ' AND datasets.project_id = ' . $_GET["project_id"];
	}
	
	$query .= ';';
	
	//echo $query;
		
	$rows = pdoDBQuery($conn,$query);
	
	//var_dump($rows);
	
	$dsdata = "";
	$dsscript = "function renderMe(){";
	

	foreach ($rows as $row)
	{
		$dsdata .= '<tr id="'.$row['udi'].'"><td>'.$row['title'].'</td></tr>';
		$dsscript .= 'addFeatureFromWKT("'. $row['the_geom'] .'",{"udi":"'.$row['udi'].'","pid":"'.$row['pid'].'"});';
		
	}
	$dsscript .="};"
	
	
	
?>

<script>
	$(document).ready(function() 
	{
		initMap('olmap',{'onlyOneFeature':false,'allowModify':false,'allowDelete':true});
		initToolbar('maptoolbar',{'showDrawTools':true,'showExit':true});
	});
	
	
	function newMap()
	{
		window.open('https://proteus.tamucc.edu/~mvandeneijnden/map/metamap.php', 'window name', '');
		return false;
		
	}
	
	<?php echo $dsscript;?>
	
	$(document).on('imready', function(e) {
		//renderMe();
		//gotoAllFeatures();
	});
	
</script>



<table width="100%" height="100%" border="1">
	<tr>
		<td colspan="2" height="50px" width="100%">
		<div id="maptoolbar" style="background: #ffffff url('/sites/all/themes/griidc/images/green/body-bg.png') 0 0 repeat-x; padding: 10px;"></div>
		</td>
	</tr>
	<tr valign="top">
		<td width="70%">
			<!--Make sure the width and height of the map are 100%-->
			<div id="olmap" style="width: 100%;height: 100%;"></div>
		</td>
		<td style="width:100%;overflow-y:scroll;" width="30%" valign="top">
		<div id="datasets" >
			<!--this is the table that contains all the datasets rows-->
			<table width="100%" >
				<tbody id="dsdata">
					<!-- Placeholder for datasets -->
					<?php //echo $dsdata ?>
				</tbody>
			</table>
		</div>
		</td>
	</tr>
</table>