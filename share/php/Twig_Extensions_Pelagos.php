<?php
// @codingStandardsIgnoreFile

namespace Slim\Views;

use Slim\Slim;

require_once 'Twig/ExtensionInterface.php';
require_once 'Twig/Extension.php';

class Twig_Extensions_Pelagos extends \Twig_Extension {
    public function getName() {
        return 'Pelagos';
    }

    public function getFilters() {
        return array(
            'removeYR1BG' => new \Twig_Filter_Method($this,'removeYR1BG'),
            'statusToImg' => new \Twig_Filter_Method($this,'statusToImg'),
            'statusToTitle' => new \Twig_Filter_Method($this,'statusToTitle'),
            'trimws' => new \Twig_Filter_Method($this,'trimws'),
            'evaluate' => new \Twig_Filter_Method($this, 'evaluate', array(
                'needs_environment' => true,
                'needs_context' => true,
                'is_safe' => array(
                    'evaluate' => true
                )
            ))
        );
    }

    public function removeYR1BG($string) {
        return preg_replace('/^Year One Block Grant - /','',$string);
    }

    public function statusToImg($status) {
        return $GLOBALS['config']['status_icons'][$status];
    }

    public function statusToTitle($status_type) {
        return $GLOBALS['config']['status_titles'][$status_type];
    }

    public function trimws($string) {
        return preg_replace('/^\s+|\s+$/','',$string);
    }

    public function evaluate( \Twig_Environment $environment, $context, $string ) {
        $loader = $environment->getLoader( );
        $parsed = $this->parseString( $environment, $context, $string );
        $environment->setLoader( $loader );
        return $parsed;
    }

    protected function parseString( \Twig_Environment $environment, $context, $string ) {
        $environment->setLoader( new \Twig_Loader_String( ) );
        return $environment->render( $string, $context );
    }
}

?>
