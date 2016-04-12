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
            new \Twig_SimpleFilter(
                'removeYR1BG',
                array(self::class, 'removeYR1BG')
            ),
            new \Twig_SimpleFilter(
                'statusToImg',
                array(self::class, 'statusToImg')
            ),
            new \Twig_SimpleFilter(
                'statusToTitle',
                array(self::class, 'statusToTitle')
            ),
            new \Twig_SimpleFilter(
                'trimws',
                array(self::class, 'trimws')
            ),
            new \Twig_SimpleFilter(
                'evaluate',
                array(self::class, 'evaluate'),
                array(
                    'needs_environment' => true,
                    'needs_context' => true,
                    'is_safe' => array(
                        'evaluate' => true,
                    )
                )
            ),
        );
    }

    /**
     * Remove the text "Year One Block Grant - " from the front of a string.
     *
     * @param string $string The string to filter.
     *
     * @return string The filtered string.
     */
    public static function removeYR1BG($string)
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
    public static function statusToImg($status)
    {
        return $GLOBALS['config']['status_icons'][$status];
    }

    /**
     * Return that status title that corresponds to the given status type.
     *
     * @param string $statusType The status type.
     *
     * @return string The status title.
     */
    public static function statusToTitle($statusType)
    {
        return $GLOBALS['config']['status_titles'][$statusType];
    }

    /**
     * Trim whitespace from the beginning and ending of a string.
     *
     * @param string $string The string to trim.
     *
     * @return string The trimmed string.
     */
    public static function trimws($string)
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
    public static function evaluate(\Twig_Environment $environment, array $context, $string)
    {
        $loader = $environment->getLoader();
        $parsed = self::parseString($environment, $context, $string);
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
    protected static function parseString(\Twig_Environment $environment, array $context, $string)
    {
        $environment->setLoader(new \Twig_Loader_String());
        return $environment->render($string, $context);
    }
}
