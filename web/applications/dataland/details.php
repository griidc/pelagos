<?php
// @codingStandardsIgnoreFile
error_reporting(E_ALL);
ini_set("display_errors", 1);

# load global pelagos config
$GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);
# load Common library from global share
require_once($GLOBALS['config']['paths']['share'].'/php/Common.php');
# check for local config file
if (file_exists('config.ini')) {
    # merge local config with global config
    $GLOBALS['config'] = configMerge($GLOBALS['config'], parse_ini_file('config.ini', true));
}
# add pelagos/share/php to the include path
set_include_path(get_include_path() . PATH_SEPARATOR . $GLOBALS['config']['paths']['share'] . '/php');


require_once('DBUtils.php');

drupal_add_css("$_SERVER[SCRIPT_NAME]/includes/css/xmlverbatim.css",array('type'=>'external'));
drupal_add_css("$_SERVER[SCRIPT_NAME]/includes/css/details.css",array('type'=>'external'));
drupal_add_css("$_SERVER[SCRIPT_NAME]/includes/css/status.css",array('type'=>'external'));
drupal_add_css('/data-discovery/includes/css/dataset_download.css',array('type'=>'external'));
drupal_add_css('/data-discovery/includes/css/logins.css',array('type'=>'external'));

drupal_add_library('system', 'jquery.cookie');
drupal_add_library('system', 'ui.tabs');
drupal_add_library('system', 'ui.button');
drupal_add_library('system', 'ui.dialog');

drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/openlayers/2.13.1/OpenLayers.js',array('type'=>'external'));
drupal_add_js('//maps.google.com/maps/api/js?v=3.21&sensor=false',array('type'=>'external'));
drupal_add_js('/includes/geoviz/geoviz.js','external');
drupal_add_js('/data-discovery/js/search.js',array('type'=>'external'));

include_once 'aliasIncludes.php';
require_once 'auth.php'; # for user_is_logged_in_somehow()
include_once 'pdo.php'; # for pdoDBQuery()
require_once 'lib/DataLand/PubLink.php';

$loader = new \Twig_Loader_Filesystem('./templates');
$twig = new \Twig_Environment($loader,array('autoescape' => false));
$twig->addExtension(new \Pelagos\TwigExtensions());

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$pageLessBaseUrl="$protocol$_SERVER[SERVER_NAME]";
$server_name = $_SERVER['SERVER_NAME'];

$udi='';
$dsscript='';
$prow ='';
$mrow ='';
$mprow ='';

$URIs = preg_split('/\//',$_SERVER['REQUEST_URI']);

$udi = urldecode($URIs[count($URIs)-2]);
if (!preg_match('/^[A-Z][A-Z\d]\.x\d{3}.\d{3}:\d{4}$/', $udi)) {
    $udi = '';
}

$logged_in = user_is_logged_in_somehow(); # returns bool, true if logged in.

if ($udi <> '')
{

    $pconn = OpenDB("GOMRI_RW");

    # Toggle per ini file parameter the enforcement of dataset downloadability requiring accepted metadata
    $enforceMetadataRule = 0;
    if( (isset($GLOBALS['config']['system']['enforce_approved_metadata'] ) and ( $GLOBALS['config']['system']['enforce_approved_metadata'] == 1 )) ) {
        $enforceMetadataRule = 1;
    } else {
        $enforceMetadataRule = 0;
    }

    $serverTZ = ini_get('date.timezone');
    if(empty($serverTZ)) {
        $serverTZ = date_default_timezone_get();
    }

    $pquery = "
    SELECT *,

    TRIM(BOTH FROM array_to_string(theme_keywords, ', ')) AS theme_keywords_string,

    CASE WHEN metadata_view.registry_id IS NULL AND datasets.geom IS NULL THEN 'baseMap'
         WHEN metadata_view.registry_id IS NULL AND datasets.geom IS NOT NULL THEN ST_AsText(datasets.geom)
         WHEN metadata_view.registry_id IS NOT NULL AND metadata_view.geom IS NOT NULL THEN ST_AsText(metadata_view.geom)
         WHEN metadata_view.registry_id IS NOT NULL AND metadata_view.geom IS NULL AND metadata_view.extent_description IS NULL THEN 'baseMap'
         WHEN metadata_view.registry_id IS NOT NULL AND metadata_view.geom IS NULL AND metadata_view.extent_description IS NOT NULL THEN 'labOnly'
    END AS geom_data,

    CASE WHEN metadata_view.abstract IS NOT NULL THEN metadata_view.abstract
         WHEN metadata_view.abstract IS NULL AND registry_view.dataset_abstract IS NOT NULL THEN registry_view.dataset_abstract
         WHEN metadata_view.abstract IS NULL AND registry_view.dataset_abstract IS NULL AND datasets.abstract IS NOT NULL THEN datasets.abstract
    END AS abstract,

    CASE WHEN registry_view.dataset_udi IS NULL THEN datasets.dataset_udi ELSE registry_view.dataset_udi
    END AS dataset_udi,

    CASE WHEN metadata_view.title IS NOT NULL THEN metadata_view.title
         WHEN registry_view.dataset_title IS NOT NULL THEN registry_view.dataset_title
         ELSE  datasets.title
    END AS title,

    CASE WHEN status = 2 THEN 10
         WHEN status = 1 THEN 1
         ELSE 0
    END AS identified,

    CASE WHEN registry_view.registry_id IS NULL THEN 0
         WHEN url_data IS NULL OR url_data = '' THEN 1
         ELSE 10
    END AS registered,

    CASE WHEN metadata_dl_status = 'Completed' THEN
             CASE WHEN metadata_status = 'Accepted' THEN 10
                  WHEN metadata_status = 'InReview' THEN 2
                  ELSE 1
             END
         ELSE 0
    END AS metadata,

    CASE WHEN dataset_download_status = 'Completed' THEN
             CASE WHEN (metadata_status <> 'Accepted' AND '$enforceMetadataRule' = '1') THEN 4
                  WHEN access_status = 'None' THEN 10
                  WHEN access_status = 'Approval' THEN 9
                  WHEN access_status = 'Restricted' THEN 8
                  ELSE 0
             END
         WHEN dataset_download_status = 'RemotelyHosted' THEN
             CASE WHEN (metadata_status <> 'Accepted' AND '$enforceMetadataRule' = '1') THEN 4
                  WHEN access_status = 'None' THEN 7
                  WHEN access_status = 'Approval' THEN 6
                  WHEN access_status = 'Restricted' THEN 5
                  ELSE 0
             END
         ELSE 0
    END AS available,

    CASE
        WHEN registry_view.submittimestamp IS NULL AND datasets.last_edit IS NOT NULL THEN
            to_char(timezone('UTC', timezone('$serverTZ', datasets.last_edit)), 'Mon DD YYYY HH24:MI') || ' UTC'
        WHEN registry_view.submittimestamp IS NOT NULL THEN
            to_char(timezone('UTC', timezone('$serverTZ', registry_view.submittimestamp)), 'Mon DD YYYY HH24:MI') || ' UTC'
        ELSE
            NULL
    END AS lastupdatetimestamp

    FROM datasets
    LEFT JOIN registry_view ON registry_view.dataset_udi = datasets.dataset_udi
    LEFT JOIN metadata_view ON registry_view.registry_id = metadata_view.registry_id
    WHERE datasets.dataset_udi = '$udi' LIMIT 1
    ;
    ";

    $prow = pdoDBQuery($pconn,$pquery);

    $prow = $prow[0];

    if($prow["doi"]) {
        $prow["noheader_doi"] = preg_replace("/doi:/",'',$prow["doi"]);
    }

    if ($prow["geom_data"] == 'labOnly') {
        $dsscript = "dlmap.addImage('$_SERVER[SCRIPT_NAME]/includes/images/labonly.png',0.4);dlmap.makeStatic();";
    } elseif ($prow["geom_data"] != 'baseMap') { //  add the geometry from the data. Either datasets or metadata
        $dsscript = 'dlmap.addFeatureFromWKT("' . $prow['geom_data'] . '",{"udi":"' . $prow['dataset_udi'] . '"});dlmap.gotoAllFeatures();';
    } //  else




    //  fall through and only the base map will show

    $mconn = OpenDB("RIS_RO");

    $mquery = "  SELECT * FROM Projects
    JOIN Programs ON Projects.Program_ID = Programs.Program_ID
    LEFT OUTER JOIN FundingSource ON  FundingSource.Fund_ID = Programs.Program_FundSrc
    WHERE Programs.Program_ID = '".$prow["project_id"]."'
    AND Projects.Project_ID = '".$prow["task_uid"]."'
    ;
    ";

    $mrow = pdoDBQuery($mconn,$mquery);

    $mrow = $mrow[0];

    $mquery = "
    SELECT
	People_FirstName, People_LastName,
    Institution_Name, Department_URL, Department_Name,
    Department_Addr1, Department_Addr2,
    Department_City, Department_State, Department_Zip, Department_Country, People_Email
    FROM People
    LEFT OUTER JOIN Institutions ON Institutions.Institution_ID = People.People_Institution
    LEFT OUTER JOIN Departments ON Departments.Department_ID = People.People_Department

    WHERE People_ID = ".$prow["primary_poc"]."
    ;
    ";

    $mprow = pdoDBQuery($mconn,$mquery);

    $mprow = $mprow[0];
    $publink = new \DataLand\PubLink();
    $publinks = $publink->getLinksArray($udi);
    $publinkCount = sizeof($publinks);
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


if ($prow != null)
{

?>

<script>

$(document).ready(function() {
    // If cookie is set and we are logged in (per php variable as a literal in js) remove it and initiate download
    if ((<?php if ($logged_in) { print "1"; } else { print "0";} ?>) && (typeof $.cookie('dl_attempt_udi_cookie') != 'undefined')) {
        var dl_cookie = $.cookie('dl_attempt_udi_cookie');
        $.cookie("dl_attempt_udi_cookie", null, { path: "/", domain: "<?php print "$server_name"; ?>" });
        if (dl_cookie != null) {
            showDatasetDownload(dl_cookie);
        }
    }
});

var dlmap = new GeoViz();

(function ($) {
    $(function() {

        resizeMap();

        $( window ).resize(function()
        {
            resizeMap();
        });

        $("#rawxml").width($(document).width()*.90);

        if( <?php echo $publinkCount ?> > 0) {
            $("#tabs").tabs({ heightStyle: "content" });
        } else {
            $("#tabs").tabs({ heightStyle: "content", disabled: [ 2 ] });
        }

        $("#xmlradio").buttonset();

        $("#xmlraw").click(function() {
            $("#formatedxml").hide();
            $("#rawxml").show();
        });

        $("#xmlformated").click(function() {
            $("#formatedxml").show();
            $("#rawxml").hide();
        });

        dlmap.initMap('dlolmap',{'onlyOneFeature':false,'allowModify':false,'allowDelete':false,'staticMap':false,'labelAttr':'udi'});

        $("#downloadds").button().click(function() {
            if(<?php echo "\"".$prow['dataset_download_status']."\"" ?> == "RemotelyHosted") {
                showDatasetDownloadExternal('<?php echo $udi;?>')
            } else {
                showDatasetDownload('<?php echo $udi;?>')
            }
        });

        $("#download_dialog").dialog({
            autoOpen: false,
            buttons: {
                OK: function() {
                    $(this).dialog("close");
                }
            },
            modal: true,
            resizable:false
        });

        $("#downloaddsden").button().click(function() {
            $("#download_dialog").dialog('option', 'title', 'Dataset Not Available');
            $("#download_dialog").html('This dataset is not available for download.');
            $("#download_dialog").dialog('open');
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
                classes: "qtip-default qtip-shadow qtip-tipped"
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

        $("#downloaddsden").qtip({
            position: {
                adjust: {
                    method: "flip flip"
                },
                my: "bottom right",
                at: "top left",
                viewport: $(window)
            },
            content: {
                text: 'This dataset is not currently available for download.'
            }
        });

        $("#downloaddsdenmd").qtip({
            position: {
                adjust: {
                    method: "flip flip"
                },
                my: "bottom right",
                at: "top left",
                viewport: $(window)
            },
            content: {
                text: 'This dataset is not currently available for download until its metadata is approved.'
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

        $("#metadatadl-dis").qtip({
            position: {
                adjust: {
                    method: "flip flip"
                },
                my: "bottom right",
                at: "top left",
                viewport: $(window)
            },
            content: {
                text: 'Metadata will be available after it is approved.'
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
            }
        });

    });

    function resizeMap()
    {
        $("#dlolmap").width($(document).width()*.40);
        mapscreenhgt = $("#dlolmap").width()/4*3;
        summaryhgt = $("#summary").height()
        if (mapscreenhgt > summaryhgt)
        {
            $("#dlolmap").height(mapscreenhgt)
        }
        else
        {
            $("#dlolmap").height(summaryhgt)
        }
    };

    $(document).on('imready', function(e) {
        <?php echo $dsscript;?>
    });
})(jQuery);
</script>

<div id="dataset_download" style="display: none;">
    <div id="dataset_download_close"><input type="image" src="/data-discovery/includes/images/close.gif" onclick="jQuery('#dataset_download').hide();"></div>
    <div id="dataset_download_content">

    </div>
</div>

<div id="pre_login" style="display: none;">
    <div id="pre_login_close"><input type="image" src="/data-discovery/includes/images/close.gif" onclick="jQuery('#pre_login').hide();"></div>
    <div id="pre_login_content">
        <table cellpadding="10">
            <tbody><tr>
                <td colspan="3" align="center">
                    <h3>Please log in for access to this data.</h3>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <div id="griidc-logo">
                        <a class="redir_url" href="/auth/cas?dest=<?php echo "$_SERVER[REQUEST_URI]";?>"><img src="/data-discovery/includes/images/GRIIDC-logo.png" alt="GRIIDC logo"></a>
                    </div>
                    <div>
                        GoMRI Users, please use your<br>
                        <a class="redir_url" href="/auth/cas?dest=<?php echo "$_SERVER[REQUEST_URI]";?>">GRIIDC login</a> to download data.
                    </div>
                </td>
                <td><img src="/data-discovery/includes/images/vbar.png"></td>
                <td align="center">
                    <div>
                        <a href="/auth/oauth2/google?dest=<?php echo "$_SERVER[REQUEST_URI]";?>"><img src="/data-discovery/includes/images/googleauth.png" alt="google auth logo"></a>
                    </div>
                    <div>
                        Members of the public may use their<br>
                        <a href="/auth/oauth2/google?dest=<?php echo "$_SERVER[REQUEST_URI]";?>">Google login</a> to download data.
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="3" align="center">
                    <button onclick="jQuery('#pre_login').hide();" style="font-weight:bold;">Cancel</button>
                </td>
            </tr>
        </tbody></table>
    </div>
</div>

<table border="0" width="100%">
    <tr>
        <td width="40%">
            <div id="dlolmap" style="width: 640px;height: 480px;"></div>

        </td>
        <td style="padding:10px;" width="60%" valign="top">
            <div id="summary">
            <?php
            $dl_ok = 0;
            # if either metadata has been approved, or we are not enforcing rule, or flag not set in ini altogether THEN ok to download, otherwise not.
            if ($prow['metadata_status'] == 'Accepted') {
                $dl_ok = 1;
            } elseif  (isset($GLOBALS['config']['system']['enforce_approved_metadata'] ) and ( $GLOBALS['config']['system']['enforce_approved_metadata'] == 0 )) {
                $dl_ok = 1;
            } else {
                $dl_ok = 0;
            }
            $dataset_available = 0;
            # check if dataset file has been downloaded successfully or marked as remotely hosted
            if (in_array($prow['dataset_download_status'], array('Completed','RemotelyHosted'))) {
                $dataset_available = 1;
            }
            echo $twig->render(
                'summary.html',
                array(
                    'pdata' => $prow,
                    'mdata' => $mrow,
                    'mpdata' => $mprow,
                    'baseurl' => $_SERVER['SCRIPT_NAME'],
                    'dl_ok' => $dl_ok,
                    'dataset_available' => $dataset_available
                )
            );
            ?>
            </div>
        </td>
        </tr>
</table>

<div>
    <div id="tabs" style="width:100%">
        <ul>
            <li><a href="#tabs-1">Details</a></li>
            <li><a href="#tabs-2">Metadata</a></li>
            <li><a href="#tabs-3">Publications</a></li>
            <!--
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

            $xml = '';
            $xsl = 'xsl/xml-to-html-ISO.xsl';

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
        <div class="tabb" id="tabs-3">
            <?php
                echo $twig->render('publications.html', array('publinks' => $publinks));
            ?>
        </div>
    </div>
</div>

<div id="download_dialog"></div>
<?php
}
else
{
    #Has NO DIF, and NO registry
?>
<p>
<h1>Dataset not found</h1>
No dataset has been identified or registered with the UDI: <?php echo "$udi";?><br/>
If you are experiencing difficulties, please contact <a href="mailto:griidc@gomri.org">GRIIDC</a>.
</p>

<?php };
