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
            'removeYR1BG' => new Twig_Filter_Method($this, 'removeYR1BG'),
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

TwigView::$twigExtensions = array(
    'Twig_Extensions_Slim',
    'Twig_Extensions_GRIIDC'
);

?>
