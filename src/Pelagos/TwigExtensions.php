<?php

namespace Pelagos;

/**
 * Custom Twig extensions.
 */
class TwigExtensions extends \Twig_Extension
{
    /**
     * Return the name of this extension set.
     *
     * @return string The name of this extension set.
     */
    public function getName()
    {
        return 'Pelagos';
    }

    /**
     * Return a list of filters.
     *
     * @return array A list of Twig filters.
     */
    public function getFilters()
    {
        return array(
            'removeYR1BG' => new \Twig_Filter_Method(
                $this,
                'removeYR1BG'
            ),
            'statusToImg' => new \Twig_Filter_Method(
                $this,
                'statusToImg'
            ),
            'statusToTitle' => new \Twig_Filter_Method(
                $this,
                'statusToTitle'
            ),
            'trimws' => new \Twig_Filter_Method(
                $this,
                'trimws'
            ),
            'evaluate' => new \Twig_Filter_Method(
                $this,
                'evaluate',
                array(
                    'needs_environment' => true,
                    'needs_context' => true,
                    'is_safe' => array(
                        'evaluate' => true
                    )
                )
            )
        );
    }

    /**
     * Remove the text "Year One Block Grant - " from the front of a string.
     *
     * @param string $string The string to filter.
     *
     * @return string The filtered string.
     */
    public function removeYR1BG($string)
    {
        return preg_replace('/^Year One Block Grant - /', '', $string);
    }

    /**
     * Return that status icon that corresponds to the given status.
     *
     * @param integer $status The status number.
     *
     * @return string The status icon name.
     */
    public function statusToImg($status)
    {
        return $GLOBALS['config']['status_icons'][$status];
    }

    /**
     * Return that status title that corresponds to the given status type.
     *
     * @param string $status_type The status type.
     *
     * @return string The status title.
     */
    public function statusToTitle($status_type)
    {
        return $GLOBALS['config']['status_titles'][$status_type];
    }

    /**
     * Trim whitespace from the beginning and ending of a string.
     *
     * @param string $string The string to trim.
     *
     * @return string The trimmed string.
     */
    public function trimws($string)
    {
        return preg_replace('/^\s+|\s+$/', '', $string);
    }

    /**
     * Evaluate Twig commands in a string.
     *
     * @param \Twig_Environment $environment The Twig environment.
     * @param array             $context     The Twig context.
     * @param string            $string      The string to evaluate.
     *
     * @return string The evaluated string.
     */
    public function evaluate(\Twig_Environment $environment, array $context, $string)
    {
        $loader = $environment->getLoader();
        $parsed = $this->parseString($environment, $context, $string);
        $environment->setLoader($loader);
        return $parsed;
    }

    /**
     * Parse Twig commands in a string.
     *
     * @param \Twig_Environment $environment The Twig environment.
     * @param array             $context     The Twig context.
     * @param string            $string      The string to parse.
     *
     * @return string The parsed string.
     */
    protected function parseString(\Twig_Environment $environment, array $context, $string)
    {
        $environment->setLoader(new \Twig_Loader_String());
        return $environment->render($string, $context);
    }
}
