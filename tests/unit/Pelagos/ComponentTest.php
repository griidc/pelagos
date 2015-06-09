<?php

namespace Pelagos;

/**
  * Mock the drupal_add_js function to just print the URL.
  * The URL will be preceded with "drupal_add_js: " and be terminated with a newline.
  *
  * @param $url string The URL to the javascript file.
  */
function drupal_add_js($url)
{
    print "drupal_add_js: $url\n";
}

/**
  * Mock the drupal_add_css function to just print the URL.
  * The URL will be preceded with "drupal_add_css: " and be terminated with a newline.
  *
  * @param $url string The URL to the CSS file.
  */
function drupal_add_css($url)
{
    print "drupal_add_css: $url\n";
}

/**
  * Mock the drupal_add_libray function to just print the module and library name.
  * The URL will be preceded with "drupal_add_library: " and be terminated with a newline.
  * The module and libray name will be separated with ::
  *
  * @param $module string The name of the module that registered the library.
  * @param $name string The name of the library to add.
  */
function drupal_add_library($module, $name)
{
    print "drupal_add_library: $module::$name\n";
}

/**
  * Mock the drupal_exit function to just print drupal_exit followed by a newline.
  */
function drupal_exit()
{
    print "drupal_exit\n";
}

/**
 * Unit tests for Pelagos\Component.
 *
 * @group Pelagos
 * @group Pelagos\Component
 */
class ComponentTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Pelagos\Component $component Class variable to hold an instance of \Pelagos\Component to test against */
    protected $component;

    /** @var string $basePath A URL base path for Pelagos for testing. **/
    protected static $basePath = '/pelagos-base';

    /** @var string $path A URL path to this component for testing. **/
    protected static $path = '/pelagos-base/applications/my-component';

    /** @var string $title A page title for testing. **/
    protected static $title = 'Foo Bar Baz';

    /**
     * Set up method.
     * Alias mock \Pelagos\Persistance::createEntityManager() to return a mock \Doctrine\ORM\EntityManager
     * Create an instance of \Pelagos\Component and save it in $this->component
     */
    public function setUp()
    {
        require_once __DIR__ . '/../../helpers/TestUser.php';
        $GLOBALS['pelagos']['base_path'] = self::$basePath;
        $GLOBALS['pelagos']['component_path'] = self::$path;
        \Mockery::mock(
            'alias:\Pelagos\Persistance',
            array(
                'createEntityManager' => \Mockery::mock(
                    '\Doctrine\ORM\EntityManager'
                ),
            )
        );
        $this->component = new \Pelagos\Component;
    }

    /**
     * Test retrieving the entity manager from the component.
     * Checks to see that we get an instance of \Doctrine\ORM\EntityManager back.
     */
    public function testGetEntityManager()
    {
        $entityManager = $this->component->getEntityManager();
        $this->assertInstanceOf('\Doctrine\ORM\EntityManager', $entityManager);
    }

    /**
     * Test adding a javascript file with a relative path (relative to the component path).
     */
    public function testAddJSRelativePath()
    {
        $this->expectOutputString('drupal_add_js: ' . self::$path . "/static/js/foo.js\n");
        $this->component->addJS('static/js/foo.js');
    }

    /**
     * Test adding a javascript file with an absolute path (relative to the base path).
     */
    public function testAddJSAbsolutePath()
    {
        $this->expectOutputString('drupal_add_js: ' . self::$basePath . "/static/js/bar.js\n");
        $this->component->addJS('/static/js/bar.js');
    }

    /**
     * Test adding a javascript file with a full url with the http protocol.
     */
    public function testAddJSFullUrlHTTP()
    {
        $this->expectOutputString("drupal_add_js: http://cdn.foo.bar/js/baz.js\n");
        $this->component->addJS('http://cdn.foo.bar/js/baz.js');
    }

    /**
     * Test adding a javascript file with a full url with the https protocol.
     */
    public function testAddJSFullUrlHTTPS()
    {
        $this->expectOutputString("drupal_add_js: https://cdn.foo.bar/js/baz.js\n");
        $this->component->addJS('https://cdn.foo.bar/js/baz.js');
    }

    /**
     * Test adding a javascript file with a full url with no protocol (protocol relative).
     */
    public function testAddJSFullUrlProtocolRelative()
    {
        $this->expectOutputString("drupal_add_js: //cdn.foo.bar/js/baz.js\n");
        $this->component->addJS('//cdn.foo.bar/js/baz.js');
    }

    /**
     * Test adding multiple javascript files in one call by passing an array.
     */
    public function testAddJSMultiple()
    {
        $this->expectOutputString(
            'drupal_add_js: ' . self::$path . "/foo.js\n" .
            'drupal_add_js: ' . self::$path . "/bar.js\n" .
            'drupal_add_js: ' . self::$path . "/baz.js\n"
        );
        $this->component->addJS(
            array(
                'foo.js',
                'bar.js',
                'baz.js',
            )
        );
    }

    /**
     * Test adding a javascript file with a path that starts with http but is not a full url.
     * This should just end up as a relative url (relative to the component path).
     */
    public function testAddJSNonUrlHTTP()
    {
        $this->expectOutputString("drupal_add_js: " . self::$path . "/http/js/baz.js\n");
        $this->component->addJS('http/js/baz.js');
    }

    /**
     * Test adding a CSS file with a relative path (relative to the component path).
     */
    public function testAddCSSRelativePath()
    {
        $this->expectOutputString('drupal_add_css: ' . self::$path . "/static/css/foo.css\n");
        $this->component->addCSS('static/css/foo.css');
    }

    /**
     * Test adding a CSS file with an absolute path (relative to the base path).
     */
    public function testAddCSSAbsolutePath()
    {
        $this->expectOutputString('drupal_add_css: ' . self::$basePath . "/static/css/bar.css\n");
        $this->component->addCSS('/static/css/bar.css');
    }

    /**
     * Test adding a CSS file with a full url with the http protocol.
     */
    public function testAddCSSFullUrlHTTP()
    {
        $this->expectOutputString("drupal_add_css: http://cdn.foo.bar/css/baz.css\n");
        $this->component->addCSS('http://cdn.foo.bar/css/baz.css');
    }

    /**
     * Test adding a CSS file with a full url with the https protocol.
     */
    public function testAddCSSFullUrlHTTPS()
    {
        $this->expectOutputString("drupal_add_css: https://cdn.foo.bar/css/baz.css\n");
        $this->component->addCSS('https://cdn.foo.bar/css/baz.css');
    }

    /**
     * Test adding a CSS file with a full url with no protocol (protocol relative).
     */
    public function testAddCSSFullUrlProtocolRelative()
    {
        $this->expectOutputString("drupal_add_css: //cdn.foo.bar/css/baz.css\n");
        $this->component->addCSS('//cdn.foo.bar/css/baz.css');
    }

    /**
     * Test adding a CSS file with a path that starts with http but is not a full url.
     * This should just end up as a relative url (relative to the component path).
     */
    public function testAddCSSNonUrlHTTP()
    {
        $this->expectOutputString("drupal_add_css: " . self::$path . "/http/css/baz.css\n");
        $this->component->addCSS('http/css/baz.css');
    }

    /**
     * Test adding multiple CSS files in one call by passing an array.
     */
    public function testAddCSSMultiple()
    {
        $this->expectOutputString(
            'drupal_add_css: ' . self::$path . "/foo.css\n" .
            'drupal_add_css: ' . self::$path . "/bar.css\n" .
            'drupal_add_css: ' . self::$path . "/baz.css\n"
        );
        $this->component->addCSS(
            array(
                'foo.css',
                'bar.css',
                'baz.css',
            )
        );
    }

    /**
     * Test adding a library.
     */
    public function testAddLibrary()
    {
        $this->expectOutputString("drupal_add_library: system::foo.bar\n");
        $this->component->addLibrary('foo.bar');
    }

    /**
     * Test quitting.
     */
    public function testQuit()
    {
        $this->expectOutputString("drupal_exit\n");
        $this->component->quit();
    }

    /**
     * Test userIsLogged in for both cases.
     */
    public function testUserIsLoggedIn()
    {
        $this->assertFalse($this->component->userIsLoggedIn());
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->assertTrue($this->component->userIsLoggedIn());
    }

    /**
     * Test that finalize calls quit when quitOnFinalize is set to true.
     */
    public function testQuitOnFinalize()
    {
        $this->component->setQuitOnFinalize(true);
        $this->expectOutputString("drupal_exit\n");
        $this->component->finalize();
    }

    /**
     * Test that finalize doesn't call quit when quitOnFinalize is set to false.
     */
    public function testDontQuitOnFinalize()
    {
        $this->component->setQuitOnFinalize(false);
        $this->expectOutputString('');
        $this->component->finalize();
    }

    /**
     * Test that finalize doesn't call quit by default.
     */
    public function testDontQuitOnFinalizeByDefault()
    {
        $this->expectOutputString('');
        $this->component->finalize();
    }

    /**
     * Test getting environmental properties.
     */
    public function testGetEnvironmentalProperties()
    {
        $this->assertEquals(self::$basePath, $this->component->getBasePath());
        $this->assertEquals(self::$path, $this->component->getPath());
    }

    /**
     * Test setting environmental properties.
     */
    public function testSetEnvironmentalProperties()
    {
        $this->component->setTitle(self::$title);
        $this->component->finalize();
        $this->assertEquals(self::$title, $GLOBALS['pelagos']['title']);
    }
}
