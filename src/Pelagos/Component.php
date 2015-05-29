<?php

namespace Pelagos;

class Component
{
    public function addJS($js_file)
    {
        drupal_add_js($GLOBALS['pelagos']['component_path'] . "/$js_file", array('type'=>'external'));
    }

    public function addCSS($css_file)
    {
        drupal_add_css($GLOBALS['pelagos']['component_path'] . "/$css_file", array('type'=>'external'));
    }

    public function addLibrary($library)
    {
        drupal_add_library('system', $library);
    }

    public function quit()
    {
        if (function_exists('drupal_exit')) {
            drupal_exit();
        } else {
            exit;
        }
    }
}
