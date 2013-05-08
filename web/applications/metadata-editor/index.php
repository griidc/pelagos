<style>
	input[type=text] {width:400px}
	label 
	{
	font-weight: bold;
	display: block;
	width: 200px;
	float: left;
	}
	label:after {content:": "}
	
	select {width:400px}
	
	button {width:50px}
	
	helptext:
	{
		text-align:center;
		color:red;
	}
</style>

<?php
//Show Errors (debug only)
error_reporting(-1);
ini_set('display_errors', '1');

drupal_add_library('system', 'ui.datepicker');

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

<table width="40%,*">
<tr><td>
<fieldset>
<legend>Metadata</legend>
<form method="post">

<?php
	echo $myMImeta->getHTML();
?>

<input type="submit"/>
<input type="reset"/>

</form>
</fieldset>
</td>
<td></td></tr></table>
