<?php
// @codingStandardsIgnoreFile

require_once __DIR__.'/../../../vendor/autoload.php';

$GLOBALS['pelagos']['title'] = 'ISO 19115-2 Metadata Editor';

$config = parse_ini_file(__DIR__ . '/config.ini');

include_once '/opt/pelagos/share/php/aliasIncludes.php';

include 'metaData.php';
include 'loadXML.php';
include 'makeXML.php';

drupal_add_library('system', 'ui.datepicker');
drupal_add_library('system', 'ui.tabs');
drupal_add_library('system', 'ui.widget');
drupal_add_library('system', 'ui.dialog');
drupal_add_library('system', 'ui.tooltip');

drupal_add_css('includes/css/metadata.css', array('type'=>'external'));
drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.11.1/jquery.validate.min.js', array('type'=>'external'));
//drupal_add_css('misc/ui/jquery.ui.button.css');
//drupal_add_css('misc/ui/jquery.ui.datepicker.css');
//drupal_add_css('misc/ui/jquery.ui.tabs.css');
//drupal_add_css('misc/ui/jquery.ui.dialog.css');

drupal_add_js('//cdn.jsdelivr.net/openlayers/2.13.1/OpenLayers.js', array('type'=>'external'));

drupal_add_js('//maps.google.com/maps/api/js?v=3&sensor=false&key='.$config['google_maps_api_key'].'&callback=Function.prototype', array('type'=>'external'));

drupal_add_js('includes/geoviz/geoviz.js', array('type'=>'external'));
drupal_add_js('includes/geoviz/mapWizard.js', array('type'=>'external'));

if (array_key_exists('action', $_GET) and $_GET['action'] == 'help') {
    require 'help.html';
    exit;
}

$xmldoc = null;

if (isset($_FILES["file"])) {
    if ($_FILES["file"]["error"] > 0) {
        //echo "Error: " . $_FILES["file"]["error"] . "<br>";
        $dMessage = 'Error while loading file: ' .  $_FILES["file"]["error"];
        drupal_set_message($dMessage, 'error', false);
    } else {
        //echo "Upload: " . $_FILES["file"]["name"] . "<br>";
        //echo "Type: " . $_FILES["file"]["type"] . "<br>";
        //echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
        //echo "Stored in: " . $_FILES["file"]["tmp_name"];
        $thefile = $_FILES["file"]["tmp_name"];
    }
}

if (isset($_POST)) {
    if (count($_POST)>1) {
        makeXML($_POST);
    }
}

if (isset($_GET["dataUrl"]) and !isset($_FILES["file"])) {
    $xmlURL = $_GET["dataUrl"];
    $xmldoc = loadXMLFromURL($xmlURL);

    if ($xmldoc != null and gettype($xmldoc) == 'object') {
        $dMessage = 'Successfully loaded XML from URL: ' .  $xmlURL;
        drupal_set_message($dMessage, 'status', false);
    } elseif ($xmldoc != null and $xmldoc == 415) {
        $dMessage =  'Sorry, the GRIIDC Metadata Editor is unable to load ';
        $dMessage .= 'the submitted metadata file because it is not valid ';
        $dMessage .= 'ISO 19115-2 XML. Please contact griidc@gomri.org for ';
        $dMessage .= 'assistance.';
        drupal_set_message($dMessage, 'warning', false);
        $xmldoc = null;
    } elseif ($xmldoc != null and is_array($xmldoc)) {
        $dMessage = 'Error while loading data from: ' .  $xmlURL;
        $dMessage .= "<br>This does not appear to be a valid ISO 19115-2 metadata file.";
        drupal_set_message($dMessage, 'error', false);
        $xmldoc = null;
    } else {
        $dMessage = 'Error while loading data from: ' .  $xmlURL;
        drupal_set_message($dMessage, 'error', false);
        $xmldoc = null;
    }
}

if (isset($thefile)) {
    if ($_FILES["file"]["type"] == "text/xml") {
        $xmldoc = loadXMLFromFile($thefile);
        if ($xmldoc != null and (is_array($xmldoc) or 'gmi:MI_Metadata' !== $xmldoc->documentElement->tagName)) {
            $dMessage = 'Unable to load file: ' .  $_FILES["file"]["name"];
            $dMessage .= "<br>This does not appear to be a valid ISO 19115-2 metadata file.";
            drupal_set_message($dMessage, 'error', false);
            $xmldoc = null;
        } elseif ($xmldoc === false) {
            $dMessage = 'Unable to load file: ' .  $_FILES["file"]["name"];
            drupal_set_message($dMessage, 'error', false);
            $xmldoc = null;
        } else {
            $dMessage = 'Successfully loaded file: ' .  $_FILES["file"]["name"];
            drupal_set_message($dMessage, 'status', false);
        }
    } else {
        $dMessage = 'Sorry.' .  $_FILES["file"]["name"] . ', is not an XML file!';
        drupal_set_message($dMessage, 'warning', false);
    }
}

$mMD = new metaData();

if (isset($xmldoc)) {
    $mMD->xmldoc = $xmldoc;
}

include 'MI_Metadata.php';
$myMImeta = new MI_Metadata($mMD, 'MIMeta', "metadata.xml");
$twigArray = array();
$twigArray['onReady'] = $mMD->onReady;
$twigArray['jqUIs'] = $mMD->jqUIs;
$twigArray['validateRules'] = $mMD->validateRules;
$twigArray['validateMessages'] = $mMD->validateMessages;
$twigArray['metadata_api_path'] = $config['metadata_api_path'];

echo "\n\n<script type=\"text/javascript\">\n";
$mMD->jsString .= $mMD->twig->render('js/base.js', $twigArray);
echo $mMD->jsString;
echo "</script>\n";

?>

<div>
<div style="font-size:smaller;" id="metadialog"></div>
<div style="font-size:smaller;display:none;" id="savedialog">
<span id="dialogtxt">All required fields are complete.<br/>
Your metadata file is ready for download.<br/></span>
<p>
<label for="filename">Please enter a filename</label><input type="text" id="filename" size="50">
</p>
Click OK to download.
</div>
<div style="font-size:smaller;" id="errordialog"></div>

<div id="udidialog" title="Load from UDI">
  <p>Please enter your UDI/Submission ID.</p>
  <form>
  <fieldset>
    <label for="udifld">UDI</label>
    <input size="40" type="text" name="udifld" id="udifld" class="text ui-widget-content ui-corner-all" />
  </fieldset>
  </form>
</div>

<table class="altrowstable" id="alternatecolor" width="60%" border="0">
	<tr>
		<td width="100%">
			<div id="metatoolbar" class="ui-widget-header ui-corner-all toolbarbutton">
				<button id="upload">Load from File</button>
				<button id="fromudi">Load from GRIIDC Dataset</button>
				<button id="forcesave">Save to File</button>
				<button id="startover">Clear Form</button>
				<button id="generate">Check and Save to File</button>
				<button id="helpscreen">Help</button>
			</div>
			<div id="loadfrm" style="display:none;">
			<frameset>
					Please select a file...
				<form id="uploadfrm" method="post" enctype="multipart/form-data">
					<input onfocus="uploadFile();"  id="file" name="file" type="file" />
				</form>
			</frameset>
			</div>
			<form name="metadata" id="metadata" method="post">
            <input type="hidden" name="__validated" value="0">
			<input type="hidden" id="__ldxmldoc" name="__ldxmldoc" value="<?php if (isset($mMD->xmldoc)) {
			    echo base64_encode($mMD->xmldoc->saveXML());
			};?>">
			<fieldset>
				<?php echo $myMImeta->getHTML(); ?>
			</fieldset>
			</form>
		</td>
	</tr>
</table>
</div>
