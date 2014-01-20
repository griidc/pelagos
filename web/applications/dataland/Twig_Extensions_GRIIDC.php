<?php

//namespace Slim\Views;

//use Slim\Slim;

require_once 'Twig/ExtensionInterface.php';
require_once 'Twig/Extension.php';

class Twig_Extensions_GRIIDC extends Twig_Extension {
    public function getName() {
        return 'GRIIDC';
    }

    public function getFilters() {
        return array(
            'removeYR1BG' => new \Twig_Filter_Method($this,'removeYR1BG'),
            'statusToImg' => new \Twig_Filter_Method($this,'statusToImg'),
            'statusToTitle' => new \Twig_Filter_Method($this,'statusToTitle'),
            'trimws' => new \Twig_Filter_Method($this,'trimws')
        );
    }

    public function removeYR1BG($string) {
        return preg_replace('/^Year One Block Grant - /','',$string);
    }

    public function statusToImg($status) {
        return $GLOBALS['config']['status_icons'][$status];
    }

    public function trimws($string) {
        return preg_replace('/^\s+|\s+$/','',$string);
    }

    public function statusToTitle($status_type) {
        return $GLOBALS['config']['status_titles'][$status_type];
    }
}

?>
