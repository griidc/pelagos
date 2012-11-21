<?php

require_once TwigView::$twigDirectory.'/ExtensionInterface.php';
require_once TwigView::$twigDirectory.'/Extension.php';

class Twig_Extensions_GRIIDC extends Twig_Extension
{
    public function getName()
    {
        return 'GRIIDC';
    }

    public function getFilters()
    {
        return array(
            'removeYR1BG' => new Twig_Filter_Method($this,'removeYR1BG'),
            'statusToImg' => new Twig_Filter_Method($this,'statusToImg')
        );
    }

    public function removeYR1BG($string) {
        return preg_replace('/^Year One Block Grant - /','',$string);
    }

    public function statusToImg($status) {
        return $GLOBALS['config']['status_icons'][$status];
    }
}

TwigView::$twigExtensions = array(
    'Twig_Extensions_Slim',
    'Twig_Extensions_GRIIDC'
);

?>
