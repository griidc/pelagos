<?php

namespace Pelagos;

class Component
{
    protected $quitOnFinalize = false;

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

    public function setSlimResponseHTTPStatusJSON(HTTPStatus $status)
    {
        $this->slim->response->headers->set('Content-Type', 'application/json');
        $this->slim->response->status($status->code);
        $this->slim->response->setBody($status->asJSON());
    }
}
