<?php

namespace Pelagos;

class Component
{
    public $slim;

    public function __construct()
    {
        require 'Slim/Slim.php';
        \Slim\Slim::registerAutoloader();
        $this->slim = new \Slim\Slim();
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
