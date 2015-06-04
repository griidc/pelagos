<?php

namespace Pelagos;

/**
 * A class with methods common to all Pelagos components.
 *
 * These include:
 *   - methods to add assets (JavaScript and CSS) to a page
 *   - a method to quit safely when output should be immediately flushed 
 *     and control should not be returned to the containing framework (e.g. Drupal)
 *   - a method to access the entity manager for Pelagos entities
 *
 * Each component can extend this class with a custom component class.
 */
class Component
{
    /** @var \Doctrine\ORM\EntityManager $entityManager A private class variable to hold the entity manager. */
    private $entityManager;

    /**
     * A method for adding javascript files to a page.
     * This currently only works when the component is contained by Drupal.
     *
     * @param string|array $js The relative (to component path)
     *                         or absolute (to Pelagos base path)
     *                         path the javascript file,
     *                         a full URL to the file,
     *                         or an array of any of the former.
     */
    public function addJS($js)
    {
        $url_array = $this->getUrlArray($js);
        foreach ($url_array as $js_url) {
            drupal_add_js($js_url, array('type'=>'external'));
        }
    }

    /**
     * A method for adding CSS files to a page.
     * This currently only works when the component is contained by Drupal.
     *
     * @param string|array $css The relative (to component path)
     *                          or absolute (to Pelagos base path)
     *                          path the CSS file,
     *                          a full URL to the file,
     *                          or an array of any of the former.
     */
    public function addCSS($css)
    {
        $url_array = $this->getUrlArray($css);
        foreach ($url_array as $css_url) {
            drupal_add_css($css_url, array('type'=>'external'));
        }
    }

    /**
     * A method for including libraries from the containing framework.
     * Currently, only Drupal is supported.
     */
    public function addLibrary($library)
    {
        drupal_add_library('system', $library);
    }

    /**
     * A method for quitting safely when output should be immediately flushed 
     * and control should not be returned to the containing framework.
     * Currently, only Drupal is supported, but it will simply exit if not contained by Drupal.
     */
    public function quit()
    {
        if (function_exists('drupal_exit') or function_exists('\Pelagos\drupal_exit')) {
            drupal_exit();
        } else {
            exit;
        }
    }

    /**
     * Get the entity manager.
     * This method returns the entity manager (and creates it first if doesn't exist).
     */
    public function getEntityManager()
    {
        if (!isset($this->entityManager)) {
            $this->entityManager = Persistance::createEntityManager();
        }
        return $this->entityManager;
    }

    /**
     * Private static method that tests whether a string looks like a full URL.
     */
    private static function isFullUrl($string)
    {
        if (preg_match('/^(?:https?:)?\/\//', $string)) {
            return true;
        }
        return false;
    }

    /**
     * Private method to get full urls for an asset or array of assets.
     *
     * @param $assets string|array An asset or array of assets to get full urls for.
     * @return array An array containing full urls for the assets.
     */
    private function getUrlArray($assets)
    {
        $url_array = array();
        if (is_array($assets)) {
            $asset_array = $assets;
        } else {
            $asset_array = array($assets);
        }
        foreach ($asset_array as $asset) {
            if (self::isFullUrl($asset)) {
                $url = $asset;
            } elseif (preg_match('/^\//', $asset)) {
                $url = $GLOBALS['pelagos']['base_path'] . $asset;
            } else {
                $url = $GLOBALS['pelagos']['component_path'] . "/$asset";
            }
            array_push($url_array, $url);
        }
        return $url_array;
    }
}
