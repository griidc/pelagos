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
      * @var bool $quitOnFinalize A boolean value that is used to decide whether or not
      *                           to call quit() when finalize() is called.
      */
    private $quitOnFinalize = false;

    /** @var string $basePath The URL base path for Pelagos. **/
    protected $basePath;

    /** @var string $path The URL path to this component. **/
    protected $path;

    /** @var string $title The page title. **/
    protected $title;

    /**
     * Constructor for component.
     * This initializes some properties from the environment.
     */
    public function __construct()
    {
        if (array_key_exists('pelagos', $GLOBALS)) {
            if (array_key_exists('base_path', $GLOBALS['pelagos'])) {
                $this->basePath = $GLOBALS['pelagos']['base_path'];
            }
            if (array_key_exists('component_path', $GLOBALS['pelagos'])) {
                $this->path = $GLOBALS['pelagos']['component_path'];
            }
        }
    }

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
     *
     * @param string $library Name of library.
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
     *
     * @param string $string String to test.
     * @return bool Returns true if it is a full URL, false otherwise.
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
     * @param string|array $assets An asset or array of assets to get full urls for.
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
                $url = $this->basePath . $asset;
            } else {
                $url = $this->path . "/$asset";
            }
            array_push($url_array, $url);
        }
        return $url_array;
    }

    /**
     * Setter for quitOnFinalize.
     *
     * @param bool $quitOnFinalize True to quit on finalize, False to not.
     */
    public function setQuitOnFinalize($quitOnFinalize)
    {
        $this->quitOnFinalize = $quitOnFinalize;
    }

    /**
     * Method to do various things after the component has run.
     * These include:
     * - set the page title in the environment if the title propety has been set
     * - quit if quitOnFinalize has been set to true
     */
    public function finalize()
    {
        if (isset($this->title)) {
            $GLOBALS['pelagos']['title'] = $this->title;
        }
        if ($this->quitOnFinalize) {
            $this->quit();
        }
    }

    /**
     * Method to determine if the user is logged in or not.
     *
     * @return bool Returns true if user is logged in, false otherwise.
     */
    public function userIsLoggedIn()
    {
        global $user;
        if (isset($user->name) and !empty($user->name)) {
            return true;
        }
        return false;
    }

    /**
     * Method to get the Pelagos base URL path.
     *
     * @return string The URL base path for Pelagos.
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Method to get the Pelagos component URL path.
     *
     * @return string The URL path to this component.
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Method to set the page title.
     *
     * @param string $title The page title.
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
}
