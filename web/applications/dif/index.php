<?php
// @codingStandardsIgnoreFile

require_once __DIR__ . '/../../../vendor/autoload.php';

$GLOBALS['pelagos']['title'] = 'Dataset Information Form (DIF)';

require_once 'dif.php';
include_once '../../../share/php/aliasIncludes.php';

drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.11.1/jquery.validate.min.js', array('type'=>'external'));
drupal_add_js('includes/js/dif.js', array('type'=>'external'));
drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/spin.js/2.0.1/spin.min.js', array('type'=>'external'));
drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/datejs/1.0/date.min.js', array('type'=>'external'));
drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/openlayers/2.13.1/OpenLayers.js', array('type'=>'external'));
drupal_add_js('//maps.google.com/maps/api/js?v=3.21&sensor=false', array('type'=>'external'));
drupal_add_js('/includes/geoviz/geoviz.js', array('type'=>'external'));
drupal_add_js('/includes/geoviz/mapWizard.js', array('type'=>'external'));
drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/jstree/3.0.1/jstree.min.js', array('type'=>'external'));
drupal_add_css('//cdnjs.cloudflare.com/ajax/libs/jstree/3.0.1/themes/default/style.min.css', array('type'=>'external'));
drupal_add_css('includes/css/dif.css', array('type'=>'external'));
drupal_add_library('system', 'ui.datepicker');
drupal_add_library('system', 'ui.widget');
drupal_add_library('system', 'ui.dialog');
drupal_add_library('system', 'ui.tooltip');
drupal_add_library('system', 'ui.autocomplete');


if (getUID() == "") {
    drupal_set_message('Please log in first!', 'warning', false);
    echo '<h1>Please login in first to use this form!</h1>';
} else {
    showDIFForm();
}
print<<<_html_
<div class="modal" id="spinner"></div>
_html_;
