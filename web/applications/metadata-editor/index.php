<script type="text/JavaScript">
	window.onload=function()
	{
		altRows('alternatecolor');
	}
</script>

<style type="text/css">
	table.altrowstable {
		font-family: verdana,arial,sans-serif;
		font-size:11px;
	}

	table.altrowstable td {
		border-width: 1px;
		padding: 5px;
		border-style: solid;
		border-color: #a9c6c9;
	}
	.oddrowcolor{
		background-color:#98AAAF;
	}
	.evenrowcolor{
		background-color:#C6D0D2;
	}

	input[type=text] 
	{
		
	}
	input[type=text]:focus
	{
		background-color: #FFFFCC;
	}
	
	textarea:focus
	{
	background-color: #FFFFCC;
	}
	
	pre
	{
		width:400px;
	}
	
	select:focus
	{
	background-color: #FFFFCC;
	}
	
	label 
	{
	font-weight: bold;
	display: block;
	}
	label:after {content:": "}
	
	select {width:300px}
	
	button {width:50px}
	
	#helptext:
	{
		text-align:center;
		color:red;
	}

	legend 
	{
		text-shadow: 2px 2px 3px rgba(150, 150, 150, 0.75);
		font-family:Verdana, Geneva, sans-serif;
		font-size:1.4em;
		padding: 3px;
		border-top: 1px solid #000;
		border-left: 1px solid #000;
		border-right:  1px solid #000;
		border-bottom:  1px solid #000;
		background-color: white;
	}
	
	fieldset 
	{
		border: 1px dotted #eee;
	}
	

</style>


<?php
//Show Errors (debug only)
error_reporting(-1);
ini_set('display_errors', '1');

drupal_add_library('system', 'ui.datepicker');
drupal_add_library('system', 'ui.tabs');

include 'loadXML.php';
include 'makeXML.php';

//drupal_add_css('/metadata/metadata.css',array('type'=>'external'));

drupal_add_js('/includes/jquery-validation/jquery.validate.js',array('type'=>'external'));

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
		$xpath = "";
		
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
		
		return $xmlArray;
	}
}

$mMD = new metaData();

if (isset($xmldoc))
{
	$mMD->xmldoc = $xmldoc;
}

include 'MI_Metadata.php';
$myMImeta = new MI_Metadata($mMD,'MIMeta',uniqid());

echo "\n\n<script type=\"text/javascript\">\n";
$mMD->jsString .= $mMD->twig->render('js/base.js', array('jqUIs' => $mMD->jqUIs,'validateRules' => $mMD->validateRules, 'validateMessages' => $mMD->validateMessages));
echo $mMD->jsString;
echo "</script>\n";

?>

<table class="altrowstable" id="alternatecolor" width="60%" border="0">
<tr><td width="100%">
<fieldset>
<legend>Metadata</legend>
<form name="metadata" id="metadata" method="post">

<?php
	echo $myMImeta->getHTML();
?>

</td>
</tr>
<tr><td>
	<input type="submit"/>
	<input type="reset"/>
	</form>
</td></tr>

</fieldset>

</table>

