<?php

include_once '/usr/local/share/GRIIDC/php/aliasIncludes.php';

drupal_add_js('/includes/jquery-validation/jquery.validate.js',array('type'=>'external'));
drupal_add_js('includes/js/dif.js',array('type'=>'external'));
drupal_add_js('includes/js/spin.min.js',array('type'=>'external'));
drupal_add_css('includes/css/dif.css',array('type'=>'external'));
drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/openlayers/2.13.1/OpenLayers.js',array('type'=>'external'));
drupal_add_js('//maps.google.com/maps/api/js?v=3&sensor=false',array('type'=>'external'));
//drupal_add_js('/includes/geoviz/geoviz.js',array('type'=>'external'));
drupal_add_js('/~mvandeneijnden/map/geoviz.js',array('type'=>'external'));
drupal_add_js('/~mvandeneijnden/map/mapWizard.js',array('type'=>'external'));
drupal_add_js('/~mvandeneijnden/jquery/vakata-jstree-b446e66/dist/jstree.min.js',array('type'=>'external'));
drupal_add_css('/~mvandeneijnden/jquery/vakata-jstree-b446e66/dist/themes/default/style.min.css',array('type'=>'external'));
drupal_add_library('system', 'ui.datepicker');
drupal_add_library('system', 'ui.widget');
drupal_add_library('system', 'ui.dialog');
drupal_add_library('system', 'ui.tooltip');
//drupal_add_library('system', 'ui.draggable');
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