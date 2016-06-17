<?php

namespace Pelagos\Bundle\AppBundle\Twig;

use Pelagos\Entity\DIF;

/**
 * Custom Twig extensions for Pelagos.
 */
class Extensions extends \Twig_Extension
{
    /**
     * Return the name of this extension set.
     *
     * @return string The name of this extension set.
     */
    public function getName()
    {
        return 'Pelagos Twig Extensions';
    }

    /**
     * Return the custom Twig functions.
     *
     * @return array The custom Twig functions.
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'add_js',
                array(self::class, 'addJS'),
                array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFunction(
                'add_css',
                array(self::class, 'addCSS'),
                array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFunction(
                'add_library',
                array(self::class, 'addLibrary'),
                array('is_safe' => array('html'))
            ),
        );
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
			new \Twig_SimpleFilter(
                'submittedDIFs',
                array(self::class, 'submittedDIFs')
            ),
        );
    }

    /**
     * Add a javascript file.
     *
     * @param string|array $js   The path to the javascript file or a string containing javascript code
     *                           (or an array of paths or code).
     * @param string       $type The type of script (external = file, inline = code).
     *
     * @return null|string Nothing if drupal_add_js is used, a script tag otherwise.
     */
    public static function addJS($js, $type = 'external')
    {
        if (!is_array($js)) {
            $js = array($js);
        }
        $drupal = false;
        $return = '';
        if (function_exists('drupal_add_js')) {
            $drupal = true;
            $return = null;
        }
        foreach ($js as $jsUrl) {
            if ($drupal) {
                drupal_add_js($jsUrl, array('type' => $type));
            } else {
                $return .= "<script type=\"text/javascript\" src=\"$jsUrl\"></script>\n";
            }
        }
        return $return;
    }

    /**
     * Add a CSS file.
     *
     * @param string|array $css  The path to the css file or a string containing css code
     *                           (or an array of paths or code).
     * @param string       $type The type of script (external = file, inline = code).
     *
     * @return null|string Nothing if drupal_add_css is used, a style tag otherwise.
     */
    public static function addCSS($css, $type = 'external')
    {
        if (!is_array($css)) {
            $css = array($css);
        }
        $drupal = false;
        $return = '';
        if (function_exists('drupal_add_css')) {
            $drupal = true;
            $return = null;
        }
        foreach ($css as $cssUrl) {
            if ($drupal) {
                drupal_add_css($cssUrl, array('type' => $type));
            } else {
                $return .= "<style type=\"text/css\" media=\"all\">@import url(\"$cssUrl\");</style>\n";
            }
        }
        return $return;
    }

    /**
     * Add a library.
     *
     * @param string|array $library The name of the library to add (or an array of library names).
     *
     * @return null|string Nothing if drupal_add_library is used, a list of libraries otherwise.
     */
    public static function addLibrary($library)
    {
        if (!is_array($library)) {
            $library = array($library);
        }
        $drupal = false;
        $return = '';
        if (function_exists('drupal_add_library')) {
            $drupal = true;
            $return = null;
        }
        foreach ($library as $libraryName) {
            if ($drupal) {
                drupal_add_library('system', $libraryName);
            } else {
                $return .= "$libraryName\n";
            }
        }
        return $return;
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
     * Filter for DIFs in submitted status.
     *
     * @param array $datasets A collection of datasets.
     *
     * @return string The evaluated string.
     */
    public static function submittedDIFs($datasets)
    {
		return $datasets->filter(
			function($dataset) {
				return $dataset->getDif()->getStatus() !== DIF::STATUS_UNSUBMITTED;
			}
		);
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
