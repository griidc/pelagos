<?php

namespace Pelagos;

class Component
{
    public $slim;

    protected $quitOnFinalize = false;

    public function __construct($options = array())
    {
        # load Twig
        require_once 'Twig/Autoloader.php';
        # load custom Twig extensions
        # require_once 'Twig_Extensions_Pelagos.php';
        require_once 'Slim/Slim.php';
        \Slim\Slim::registerAutoloader();
        if (!array_key_exists('Slim', $options) or !is_array($options['Slim'])) {
            $options['Slim'] = array();
        }
        if (class_exists('Twig_Autoloader')) {
            # load Twig Slim-View
            require_once 'Slim-Views/Twig.php';
            $options['Slim']['view'] = new \Slim\Views\Twig();
        }
        $this->slim = new \Slim\Slim($options['Slim']);
    }

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

    public function setQuitOnFinalize($quitOnFinalize)
    {
        $this->quitOnFinalize = $quitOnFinalize;
    }

    public function finalize()
    {
        if ($this->quitOnFinalize) {
            $this->quit();
        }
    }
}
