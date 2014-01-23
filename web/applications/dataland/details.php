<?php 
error_reporting(E_ALL);
ini_set("display_errors", 1);

include_once '/usr/local/share/GRIIDC/php/aliasIncludes.php';

drupal_add_css('/dif/includes/css/overwrite.css',array('type'=>'external'));
drupal_add_js('/includes/jquery-validation/jquery.validate.js',array('type'=>'external'));
//drupal_add_js('/includes/qTip2/jquery.qtip.min.js',array('type'=>'external'));

drupal_add_css('/data/includes/css/xmlverbatim.css',array('type'=>'external'));
drupal_add_css('/includes/qTip2/jquery.qtip.min.css',array('type'=>'external'));

drupal_add_library('system', 'ui.tabs');
drupal_add_library('system', 'ui.button');

drupal_add_css('/data/includes/css/details.css',array('type'=>'external'));
drupal_add_js('/includes/openlayers/lib/OpenLayers.js',array('type'=>'external'));
drupal_add_js('//maps.google.com/maps/api/js?v=3&sensor=false',array('type'=>'external'));
//drupal_add_js('/includes/geoviz/geoviz.js',array('type'=>'external'));
drupal_add_js('/includes/geoviz/geoviz.js',array('type'=>'external'));

$GLOBALS['config'] = parse_ini_file('config.ini',true);

require_once 'Twig_Extensions_GRIIDC.php';

include_once '/usr/local/share/GRIIDC/php/pdo.php';

require_once '/usr/share/pear/Twig/Autoloader.php';
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem('./templates');
$twig = new Twig_Environment($loader,array('autoescape' => false));
$twig->addExtension(new Twig_Extensions_GRIIDC());

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$pageLessBaseUrl="$protocol$_SERVER[SERVER_NAME]";

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
	SELECT * , ST_AsText(metadata.geom) AS \"the_geom\",
	CASE WHEN registry.dataset_title IS NULL THEN title ELSE registry.dataset_title END AS title,
	CASE WHEN status = 2 THEN 1 WHEN status = 1 THEN 2 ELSE 0 END AS identified,
	CASE WHEN status = 2 THEN 1 WHEN status = 1 THEN 2 ELSE 0 END AS identified,
	CASE WHEN registry.registry_id IS NULL THEN 0 ELSE 1 END AS registered,
	CASE WHEN metadata_dl_status IS NULL OR
		metadata_dl_status != 'Completed' OR
		url_metadata IS NULL
	THEN 0
	WHEN metadata_status = 'Accepted'
		THEN 1
	ELSE 2
	END AS metadata,
	CASE WHEN dataset_download_status IS NULL OR
		dataset_download_status != 'done' OR
		url_data IS NULL OR
		(data_source_pull = 'f' AND url_data NOT SIMILAR TO '(file://)?/sftp/data/%.dat')
	THEN 0
	WHEN access_status = 'None'
	THEN 1
	ELSE 2
	END AS available
	FROM registry
	LEFT OUTER JOIN datasets ON substr(registry.registry_id,0,17) = datasets.dataset_udi
	LEFT OUTER JOIN metadata on registry.registry_id = metadata.registry_id
	WHERE registry.registry_id LIKE '$udi%'
	ORDER BY registry.registry_id DESC
	LIMIT 1
	;
	";

	$prow = pdoDBQuery($pconn,$pquery);
	//echo $pquery;
	//var_dump($prow);

	//echo "</pre>";

	if ($prow["the_geom"] == null OR $prow == null)
	{
		if ($prow["metadata_xml"] == "")
		{
			$dsscript = "addImage('/data/includes/images/nodata.png',0.4);$('#metadatadl').button('disable');makeStatic();";
		}
		else
		{
			$dsscript = "addImage('/data/includes/images/labonly.png',0.4);makeStatic();";
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

	// echo "<pre>";
	// var_dump($prow);
	// echo "</pre>";
}
function transform($xml, $xsl) { 
	if ($xml <> "" AND $xml != null)
	{
	
		$xml_doc = new DOMDocument();
		$xml_doc->loadXML($xml);
		
		// XSL
		$xsl_doc = new DOMDocument();
		$xsl_doc->load($xsl);
		
		//var_dump($xsl_doc);
		
		// Proc
		$proc = new XSLTProcessor();
		$proc->importStylesheet($xsl_doc);
		$newdom = $proc->transformToDoc($xml_doc);
		
		//var_dump($newdom);
		
		return $newdom->saveXML();
	}
	else
	{
		return "No Metadata Available";
	}
}

?>

<!--
<script type="text/javascript" src="//code.jquery.com/jquery-1.8.2.js"></script>
<script type="text/javascript" src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<link rel="stylesheet" href="/includes/qTip2/jquery.qtip.min.css" />
<link rel="stylesheet" href="/data/includes/css/xmlverbatim.css" />
<link rel="stylesheet" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
<link type="text/css" rel="stylesheet" href="/data/includes/css/details.css" type="text/css">
<script type="text/javascript" src="/includes/openlayers/lib/OpenLayers.js"></script>
<script type="text/javascript" src="//maps.google.com/maps/api/js?v=3&sensor=false"></script>
<script type="text/javascript" src="/includes/qTip2/jquery.qtip.min.js"></script>

<script src="/includes/geoviz/geoviz.js"></script>

-->

<script>
(function ($) {
	$(function() {
		
		resizeMap();
		
		$( window ).resize(function()
		{
			resizeMap();
		});
				
		$("#rawxml").width($(document).width()*.90);
	
		$("#tabs").tabs({ heightStyle: "content" });
		
		$("#xmlradio").buttonset();
		
		$("#xmlraw").click(function() {
			$("#formatedxml").hide();
			$("#rawxml").show();
		});
		
		$("#xmlformated").click(function() {
			$("#formatedxml").show();
			$("#rawxml").hide();
		});

		initMap('olmap',{'onlyOneFeature':false,'allowModify':false,'allowDelete':false,'staticMap':false,'labelAttr':'udi'});
  
		$("#downloadds").button().click(function() {
			window.location = '<?php echo "$pageLessBaseUrl/data-discovery?filter=$udi";?>';
		});
		
		$("#metadatadl").button().click(function() {
			window.location = '<?php echo "$pageLessBaseUrl/metadata/$udi"; ?>';
		});
		
      
		
		$.fn.qtip.defaults = $.extend(true, {}, $.fn.qtip.defaults, {
            show: {
                event: "mouseenter focus",
                solo: true
            },
            hide: {
                event: "mouseleave blur",
                delay: 100,
                fixed: true
            },
            style: {
                classes: "ui-tooltip-shadow ui-tooltip-tipped"
            }
        });
        
        $("#downloadds").qtip({
            position: {
                adjust: {
                    method: "flip flip"
                },
                my: "bottom right",
                at: "top left",
                viewport: $(window)
            },
			content: {
				text: 'Download Dataset'
			}
        });
		
		$("#metadatadl").qtip({
            position: {
                adjust: {
                    method: "flip flip"
                },
                my: "bottom right",
                at: "top left",
                viewport: $(window)
            },
			content: {
				text: 'Download Metadata'
			}
        });
		
		 $('td[title]').qtip({
			position: {
				my: 'right bottom',
				at: 'middle left',
				adjust: {
					x: -2
				},
				viewport: $(window)
			},
			show: {
				event: "mouseenter focus",
				solo: true
			},
			hide: {
				fixed: true,
				delay: 100
			},
			style: {
				classes: "ui-tooltip-shadow ui-tooltip-tipped"
			}
		});
		
		$( document ).tooltip();
		
	});
	
	function resizeMap()
	{
		$("#olmap").width($(document).width()*.40);
		mapscreenhgt = $("#olmap").width()/4*3;
		summaryhgt = $("#summary").height()
		if (mapscreenhgt > summaryhgt)
		{
			$("#olmap").height(mapscreenhgt)
		}
		else
		{
			$("#olmap").height(summaryhgt)
		}
	};
	
	$(document).on('imready', function(e) {
		<?php echo $dsscript;?>
		//console.log("added");
	});
})(jQuery);
</script>
  
<table border="0" width="100%">
<tr>
<td width="40%">

<div id="olmap" styale="width: 640px;height: 480px;"></div>

</td>
<td style="padding:10px;" width="60%" valign="top">
<div id="summary">
<?php  echo $twig->render('summary.html', array('pdata' => $prow,'mdata' => $mrow,'mpdata' => $mprow)); ?>
</div>
</td>
</tr>
</table>
<!--<table width="100%">
<tr height="100%">
<td colspan="2"> -->
	<div id="tabs" style="width:100%">
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
		<div class="tabb" id="tabs-2" style="overflow:auto;word-wrap:break-word;height:100%;">
			<div id="xmlradio">
				<input type="radio" id="xmlformated" name="radio" checked="checked"><label for="xmlformated">Formatted</label>
				<input type="radio" id="xmlraw" name="radio" ><label for="xmlraw">Raw</label>
			</div>
			<p>
			<div id="formatedxml">
			<?php 
			//$xml = file_get_contents("/sftp/data/$udi/$udi.met");
				
			//$xml = "/sftp/data/$udi/$udi.met";
			$xml = '';
			$xsl = 'xsl/xml-to-html-ISO.xsl';
			//$xsl = 'xsl/xmlverbatim.xsl';
			
			if ($prow <>'')
			{
				$xml = $prow["metadata_xml"];
			}
			
			echo transform($xml,$xsl);
									
			?>
			</div>
			<div id="rawxml" style="display:none;">
				<?php 
					$xml = '';
			
					$xsl = 'xsl/xmlverbatim.xsl';
					
					if ($prow <>'')
					{
						$xml = $prow["metadata_xml"];
					}
					
					echo transform($xml,$xsl);
					
				?>
			</div>
		</p>
	</div>
</div>
<!--</td>
</tr>
</table>-->
