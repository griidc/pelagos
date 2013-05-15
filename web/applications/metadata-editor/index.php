<script type="text/javascript">
function altRows(id){
	if(document.getElementsByTagName){  
		
		var table = document.getElementById(id);  
		var rows = table.getElementsByTagName("tr"); 
		 
		for(i = 0; i < rows.length; i++){          
			if(i % 2 == 0){
				rows[i].className = "evenrowcolor";
			}else{
				rows[i].className = "oddrowcolor";
			}      
		}
	}
}
window.onload=function(){
	altRows('alternatecolor');
}
</script>

<style type="text/css">
table.altrowstable {
	font-family: verdana,arial,sans-serif;
	font-size:11px;
	color:#333333;
	border-width: 1px;
	border-color: #a9c6c9;
	border-collapse: collapse;
}
table.altrowstable th {
	border-width: 1px;
	padding: 8px;
	border-style: solid;
	border-color: #a9c6c9;
}
table.altrowstable td {
	border-width: 1px;
	padding: 8px;
	border-style: solid;
	border-color: #a9c6c9;
}
.oddrowcolor{
	background-color:#98AAAF;
}
.evenrowcolor{
	background-color:#C6D0D2;
}
</style>

<style>
	input[type=text] {width:300px}
	label 
	{
	font-weight: bold;
	display: block;
	}
	label:after {content:": "}
	
	select {width:400px}
	
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

//drupal_add_css('/metadata/metadata.css',array('type'=>'external'));

drupal_add_js('/includes/jquery-validation/jquery.validate.js',array('type'=>'external'));

if (isset($_POST))
{
	if (count($_POST)>1)
	{
		include 'makeXML.php';
		makeXML($_POST);
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
	
	public $twig;
	
	private $loader;
	
	public function __construct()
	{
		require_once '/usr/share/pear/Twig/Autoloader.php';
		Twig_Autoloader::register();
		
		$this->loader = new Twig_Loader_Filesystem('./templates');
		$this->twig = new Twig_Environment($this->loader,array('autoescape' => false));
	}
}

$mMD = new metaData();

include 'MI_Metadata.php';
$myMImeta = new MI_Metadata($mMD,'MIMeta',uniqid());

echo "\n\n<script>\n";
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

