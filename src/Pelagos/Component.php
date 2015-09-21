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
    /**
     * A private class variable to hold the entity manager.
     *
     * @var \Doctrine\ORM\EntityManager $entityManager
     */
    private $entityManager;

    /**
     * A boolean value that is used to decide whether or not to call quit() when finalize() is called.
     *
     * @var bool $quitOnFinalize
     */
    private $quitOnFinalize = false;

    /**
     * The URL base path for Pelagos.
     *
     * @var string $basePath
     */
    protected $basePath;

    /**
     * The URL path to this component.
     *
     * @var string $path
     */
    protected $path;

    /**
     * The base full URI for Pelagos.
     *
     * @var string $baseUri
     */
    protected $baseUri;

    /**
     * The full URI to this component.
     *
     * @var string $uri
     */
    protected $uri;

    /**
     * The page title.
     *
     * @var string $title
     */
    protected $title;

    /**
     * Constructor for component.
     *
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
            if (array_key_exists('base_url', $GLOBALS['pelagos'])) {
                $this->baseUri = $GLOBALS['pelagos']['base_url'];
            }
            if (array_key_exists('component_url', $GLOBALS['pelagos'])) {
                $this->uri = $GLOBALS['pelagos']['component_url'];
            }
        }
    }

    /**
     * A method for adding javascript files to a page.
     *
     * This currently only works when the component is contained by Drupal.
     *
     * @param string|array $js   The relative (to component path)
     *                           or absolute (to Pelagos base path)
     *                           path the javascript file,
     *                           a full URL to the file,
     *                           or an array of any of the former.
     *                           When the 'inline' type is specified, this is a string
     *                           (or an array of strings) containing JavaScript.
     * @param string       $type The type of JavaScript to add (external, inline, etc).
     *
     * @return void
     */
    public function addJS($js, $type = 'external')
    {
        $urlArray = $this->getUrlArray($js, $type);
        foreach ($urlArray as $jsUrl) {
            if (function_exists('drupal_add_js')) {
                drupal_add_js($jsUrl, array('type' => $type));
            }
        }
    }

    /**
     * A method for adding CSS files to a page.
     *
     * This currently only works when the component is contained by Drupal.
     *
     * @param string|array $css The relative (to component path)
     *                          or absolute (to Pelagos base path)
     *                          path the CSS file,
     *                          a full URL to the file,
     *                          or an array of any of the former.
     *
     * @return void
     */
    public function addCSS($css)
    {
        $urlArray = $this->getUrlArray($css);
        foreach ($urlArray as $cssUrl) {
            drupal_add_css($cssUrl, array('type' => 'external'));
        }
    }

    /**
     * A method for including libraries from the containing framework.
     *
     * Currently, only Drupal is supported.
     *
     * @param string|array $library Name of library or array of library names.
     *
     * @return void
     */
    public function addLibrary($library)
    {
        if (is_array($library)) {
            $libraryArray = $library;
        } else {
            $libraryArray = array($library);
        }
        foreach ($libraryArray as $libraryName) {
            drupal_add_library('system', $libraryName);
        }
    }

    /**
     * A method for quitting safely when output should be immediately flushed.
     *
     * Currently, only Drupal is supported, but it will simply exit if not contained by Drupal.
     *
     * @return void
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
     *
     * This method returns the entity manager (and creates it first if doesn't exist).
     *
     * @return \Doctrine\ORM\EntityManager The Doctrine entity manager.
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
     *
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
     * @param string       $type   The type of JavaScript to add (external, inline, etc).
     *
     * @return array An array containing full urls for the assets.
     */
    private function getUrlArray($assets, $type = 'external')
    {
        $urlArray = array();
        if (is_array($assets)) {
            $assetArray = $assets;
        } else {
            $assetArray = array($assets);
        }
        foreach ($assetArray as $asset) {
            if ($type == 'inline' or self::isFullUrl($asset)) {
                $url = $asset;
            } elseif (preg_match('/^\//', $asset)) {
                $url = $this->basePath . $asset;
            } else {
                $url = $this->path . "/$asset";
            }
            array_push($urlArray, $url);
        }
        return $urlArray;
    }

    /**
     * Setter for quitOnFinalize.
     *
     * @param boolean $quitOnFinalize True to quit on finalize, False to not.
     *
     * @return void
     */
    public function setQuitOnFinalize($quitOnFinalize)
    {
        $this->quitOnFinalize = $quitOnFinalize;
    }

    /**
     * Method to do various things after the component has run.
     *
     * These include:
     * - set the page title in the environment if the title propety has been set
     * - quit if quitOnFinalize has been set to true
     *
     * @return void
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
        if (isset($GLOBALS['user']->name) and !empty($GLOBALS['user']->name)) {
            return true;
        }
        return false;
    }

    /**
     * Method to get the currently logged in user.
     *
     * @return string The username of the currently logged in user.
     *
     * @throws \Exception When no user is logged in.
     */
    public function getLoggedInUser()
    {
        if (isset($GLOBALS['user']->name) and !empty($GLOBALS['user']->name)) {
            return $GLOBALS['user']->name;
        }
        throw new \Exception('No user is logged in');
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
     * Method to get the Pelagos full base URI.
     *
     * @return string The base full URI for Pelagos.
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * Method to get the Pelagos component full URI.
     *
     * @return string The full URI to this component.
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Method to set the page title.
     *
     * @param string $title The page title.
     *
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Method to set Pelagos globals in JavaScript.
     *
     * @return void
     */
    public function setJSGlobals()
    {
        if (isset($this->basePath)) {
            $this->addJS('var pelagosBasePath = "' . $this->basePath . '";', 'inline');
        }
        if (isset($this->path)) {
            $this->addJS('var pelagosComponentPath = "' . $this->path . '";', 'inline');
        }
        if (isset($this->baseUri)) {
            $this->addJS('var pelagosBaseUri = "' . $this->baseUri . '";', 'inline');
        }
        if (isset($this->uri)) {
            $this->addJS('var pelagosComponentUri = "' . $this->uri . '";', 'inline');
        }
    }
}
