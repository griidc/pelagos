<?php

$GLOBALS['pelagos'] = array();
$GLOBALS['pelagos']['title'] = 'Dataset Information Form (DIF)';

# make sure current working directory is the directory that this file lives in
$GLOBALS['orig_cwd'] = getcwd();
chdir(realpath(dirname(__FILE__)));

include_once '/opt/pelagos/share/php/aliasIncludes.php';

drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.11.1/jquery.validate.min.js',array('type'=>'external'));
drupal_add_js('includes/js/dif.js',array('type'=>'external'));
drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/spin.js/2.0.1/spin.min.js',array('type'=>'external'));
drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/datejs/1.0/date.min.js',array('type'=>'external'));
drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/openlayers/2.13.1/OpenLayers.js',array('type'=>'external'));
drupal_add_js('//maps.google.com/maps/api/js?v=3&sensor=false',array('type'=>'external'));
drupal_add_js('/includes/geoviz/geoviz.js',array('type'=>'external'));
drupal_add_js('/includes/geoviz/mapWizard.js',array('type'=>'external'));
drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/jstree/3.0.1/jstree.min.js',array('type'=>'external'));
drupal_add_css('//cdnjs.cloudflare.com/ajax/libs/jstree/3.0.1/themes/default/style.min.css',array('type'=>'external'));
drupal_add_css('includes/css/dif.css',array('type'=>'external'));
drupal_add_library('system', 'ui.datepicker');
drupal_add_library('system', 'ui.widget');
drupal_add_library('system', 'ui.dialog');
drupal_add_library('system', 'ui.tooltip');
drupal_add_library('system', 'ui.autocomplete');

include 'dif.php';

if (getUID() == "")
{
    drupal_set_message('Please log in first!','warning',false);
    echo '<h1>Please login in first to use this form!</h1>';
}
else
{showDIFForm();}
?>
<div class="modal" id="spinner"></div>
<?php
chdir($GLOBALS['orig_cwd']);
