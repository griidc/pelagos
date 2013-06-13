<script type="text/JavaScript">
	window.onload=function()
	{
		altRows('alternatecolor');
	}
</script>

<style type="text/css">


</style>


<?php
#Show Errors (debug only)
error_reporting(-1);
ini_set('display_errors', '1');

include_once '/usr/local/share/GRIIDC/php/aliasIncludes.php';

drupal_add_library('system', 'ui.datepicker');
drupal_add_library('system', 'ui.tabs');

include 'loadXML.php';
include 'makeXML.php';

drupal_add_css('includes/css/metadata.css',array('type'=>'external'));

drupal_add_js('/includes/jquery-validation/jquery.validate.js',array('type'=>'external'));

$xmldoc = null;

if (isset($_GET["dataUrl"]))
{
	$xmlURL = $_GET["dataUrl"];
	$xmldoc = loadXML($xmlURL);
	//$xmldoc->loadXML($xmlString);
}

if (isset($_POST))
{
	if (count($_POST)>1)
	{
		makeXML($_POST,$xmldoc);
	}
}



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

$mMD = new metaData();

if (isset($xmldoc))
{
	$mMD->xmldoc = $xmldoc;
}

include 'MI_Metadata.php';
$myMImeta = new MI_Metadata($mMD,'MIMeta',guid());

echo "\n\n<script type=\"text/javascript\">\n";
$mMD->jsString .= $mMD->twig->render('js/base.js', array('jqUIs' => $mMD->jqUIs,'validateRules' => $mMD->validateRules, 'validateMessages' => $mMD->validateMessages));
echo $mMD->jsString;
echo "</script>\n";

?>

<table class="altrowstable" id="alternatecolor" width="60%" border="0">
<tr><td width="100%">
<fieldset>

<form name="metadata" id="metadata" method="post">

<?php
	echo $myMImeta->getHTML();
?>

</td>
</tr>
<tr><td>
	<input onclick="jQuery('#metadata').valid();validateTabs();" type="submit" value="Generate Metadata File"/>
	<!input type="reset"/>
	</form>
</td></tr>

</fieldset>

</table>

