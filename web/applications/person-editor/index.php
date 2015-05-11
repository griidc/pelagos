<?php 

drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.11.1/jquery.validate.min.js', array('type'=>'external'));

drupal_add_js('/pelagos/dev/mvde/applications/person-editor/static/js/personForm.js', array('type'=>'external'));

drupal_add_library('system', 'ui.widget');
drupal_add_library('system', 'ui.dialog');

include 'personForm.html';

?>
