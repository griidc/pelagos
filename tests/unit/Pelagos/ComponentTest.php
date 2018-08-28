<?php

namespace Pelagos;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Pelagos\Component.
 *
 * @group Pelagos
 * @group Pelagos\Component
 */
class ComponentTest extends TestCase
{
    /**
     * Class variable to hold an instance of \Pelagos\Component to test against.
     *
     * @var \Pelagos\Component $component
     */
    protected $component;

    /**
     * A URL base path for Pelagos for testing.
     *
     * @var string $basePath
     */
    protected static $basePath = '/pelagos-base';

    /**
     * A URL path to this component for testing.
     *
     * @var string $path
     */
    protected static $path = '/pelagos-base/applications/my-component';

    /**
     * A base full URI for Pelagos for testing.
     *
     * @var string $baseUri
     */
    protected static $baseUri = 'http://foo.bar/pelagos-base';

    /**
     * A full URI to this component for testing.
     *
     * @var string $uri
     */
    protected static $uri = 'http://foo.bar/pelagos-base/applications/my-component';

    /**
     * A page title for testing.
     *
     * @var string $title
     */
    protected static $title = 'Foo Bar Baz';

    /**
     * Set up method.
     *
     * Alias mock \Pelagos\Persistance::createEntityManager() to return a mock \Doctrine\ORM\EntityManager
     * Create an instance of \Pelagos\Component and save it in $this->component
     *
     * @return void
     */
    public function setUp()
    {
        require_once __DIR__ . '/../../helpers/TestUser.php';
        $GLOBALS['pelagos']['base_path'] = self::$basePath;
        $GLOBALS['pelagos']['component_path'] = self::$path;
        $GLOBALS['pelagos']['base_url'] = self::$baseUri;
        $GLOBALS['pelagos']['component_url'] = self::$uri;
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
     * Test adding a javascript file with a relative path (relative to the component path).
     *
     * @return void
     */
    public function testAddJSRelativePath()
    {
        $this->expectOutputString('drupal_add_js: ' . self::$path . "/static/js/foo.js\n");
        $this->component->addJS('static/js/foo.js');
    }

    /**
     * Test adding a javascript file with an absolute path (relative to the base path).
     *
     * @return void
     */
    public function testAddJSAbsolutePath()
    {
        $this->expectOutputString('drupal_add_js: ' . self::$basePath . "/static/js/bar.js\n");
        $this->component->addJS('/static/js/bar.js');
    }

    /**
     * Test adding a javascript file with a full url with the http protocol.
     *
     * @return void
     */
    public function testAddJSFullUrlHTTP()
    {
        $this->expectOutputString("drupal_add_js: http://cdn.foo.bar/js/baz.js\n");
        $this->component->addJS('http://cdn.foo.bar/js/baz.js');
    }

    /**
     * Test adding a javascript file with a full url with the https protocol.
     *
     * @return void
     */
    public function testAddJSFullUrlHTTPS()
    {
        $this->expectOutputString("drupal_add_js: https://cdn.foo.bar/js/baz.js\n");
        $this->component->addJS('https://cdn.foo.bar/js/baz.js');
    }

    /**
     * Test adding a javascript file with a full url with no protocol (protocol relative).
     *
     * @return void
     */
    public function testAddJSFullUrlProtocolRelative()
    {
        $this->expectOutputString("drupal_add_js: //cdn.foo.bar/js/baz.js\n");
        $this->component->addJS('//cdn.foo.bar/js/baz.js');
    }

    /**
     * Test adding multiple javascript files in one call by passing an array.
     *
     * @return void
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
     *
     * This should just end up as a relative url (relative to the component path).
     *
     * @return void
     */
    public function testAddJSNonUrlHTTP()
    {
        $this->expectOutputString('drupal_add_js: ' . self::$path . "/http/js/baz.js\n");
        $this->component->addJS('http/js/baz.js');
    }

    /**
     * Test adding a CSS file with a relative path (relative to the component path).
     *
     * @return void
     */
    public function testAddCSSRelativePath()
    {
        $this->expectOutputString('drupal_add_css: ' . self::$path . "/static/css/foo.css\n");
        $this->component->addCSS('static/css/foo.css');
    }

    /**
     * Test adding a CSS file with an absolute path (relative to the base path).
     *
     * @return void
     */
    public function testAddCSSAbsolutePath()
    {
        $this->expectOutputString('drupal_add_css: ' . self::$basePath . "/static/css/bar.css\n");
        $this->component->addCSS('/static/css/bar.css');
    }

    /**
     * Test adding a CSS file with a full url with the http protocol.
     *
     * @return void
     */
    public function testAddCSSFullUrlHTTP()
    {
        $this->expectOutputString("drupal_add_css: http://cdn.foo.bar/css/baz.css\n");
        $this->component->addCSS('http://cdn.foo.bar/css/baz.css');
    }

    /**
     * Test adding a CSS file with a full url with the https protocol.
     *
     * @return void
     */
    public function testAddCSSFullUrlHTTPS()
    {
        $this->expectOutputString("drupal_add_css: https://cdn.foo.bar/css/baz.css\n");
        $this->component->addCSS('https://cdn.foo.bar/css/baz.css');
    }

    /**
     * Test adding a CSS file with a full url with no protocol (protocol relative).
     *
     * @return void
     */
    public function testAddCSSFullUrlProtocolRelative()
    {
        $this->expectOutputString("drupal_add_css: //cdn.foo.bar/css/baz.css\n");
        $this->component->addCSS('//cdn.foo.bar/css/baz.css');
    }

    /**
     * Test adding a CSS file with a path that starts with http but is not a full url.
     *
     * This should just end up as a relative url (relative to the component path).
     *
     * @return void
     */
    public function testAddCSSNonUrlHTTP()
    {
        $this->expectOutputString('drupal_add_css: ' . self::$path . "/http/css/baz.css\n");
        $this->component->addCSS('http/css/baz.css');
    }

    /**
     * Test adding multiple CSS files in one call by passing an array.
     *
     * @return void
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
     *
     * @return void
     */
    public function testAddLibrary()
    {
        $this->expectOutputString("drupal_add_library: system::foo.bar\n");
        $this->component->addLibrary('foo.bar');
    }

    /**
     * Test adding multiple libraries in one call by passing an array.
     *
     * @return void
     */
    public function testAddLibraryMultiple()
    {
        $this->expectOutputString(
            "drupal_add_library: system::foo\n" .
            "drupal_add_library: system::bar\n" .
            "drupal_add_library: system::baz\n"
        );
        $this->component->addLibrary(
            array(
                'foo',
                'bar',
                'baz',
            )
        );
    }

    /**
     * Test quitting.
     *
     * @return void
     */
    public function testQuit()
    {
        $this->expectOutputString("drupal_exit\n");
        $this->component->quit();
    }

    /**
     * Test userIsLogged in for both cases.
     *
     * @return void
     */
    public function testUserIsLoggedIn()
    {
        $this->assertFalse($this->component->userIsLoggedIn());
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->assertTrue($this->component->userIsLoggedIn());
    }

    /**
     * Test getLoggedInUser.
     *
     * @return void
     */
    public function testGetLoggedInUser()
    {
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->assertEquals('test', $this->component->getLoggedInUser());
    }

    /**
     * Test getLoggedInUser when no user is logged in.
     *
     * @expectedException \Exception
     *
     * @return void
     */
    public function testGetLoggedInUserNotLoggedIn()
    {
        $GLOBALS['user'] = '';
        $this->component->getLoggedInUser();
    }

    /**
     * Test that finalize calls quit when quitOnFinalize is set to true.
     *
     * @return void
     */
    public function testQuitOnFinalize()
    {
        $this->component->setQuitOnFinalize(true);
        $this->expectOutputString("drupal_exit\n");
        $this->component->finalize();
    }

    /**
     * Test that finalize doesn't call quit when quitOnFinalize is set to false.
     *
     * @return void
     */
    public function testDontQuitOnFinalize()
    {
        $this->component->setQuitOnFinalize(false);
        $this->expectOutputString('');
        $this->component->finalize();
    }

    /**
     * Test that finalize doesn't call quit by default.
     *
     * @return void
     */
    public function testDontQuitOnFinalizeByDefault()
    {
        $this->expectOutputString('');
        $this->component->finalize();
    }

    /**
     * Test getting environmental properties.
     *
     * @return void
     */
    public function testGetEnvironmentalProperties()
    {
        $this->assertEquals(self::$basePath, $this->component->getBasePath());
        $this->assertEquals(self::$path, $this->component->getPath());
        $this->assertEquals(self::$baseUri, $this->component->getBaseUri());
        $this->assertEquals(self::$uri, $this->component->getUri());
    }

    /**
     * Test setting environmental properties.
     *
     * @return void
     */
    public function testSetEnvironmentalProperties()
    {
        $this->component->setTitle(self::$title);
        $this->component->finalize();
        $this->assertEquals(self::$title, $GLOBALS['pelagos']['title']);
    }

    /**
     * Test setting Pelagos globals in JavaScript.
     *
     * @return void
     */
    public function testSetJSGlobals()
    {
        $this->expectOutputString(
            'drupal_add_js: var pelagosBasePath = "' . self::$basePath . "\";\n" .
            'drupal_add_js: var pelagosComponentPath = "' . self::$path . "\";\n" .
            'drupal_add_js: var pelagosBaseUri = "' . self::$baseUri . "\";\n" .
            'drupal_add_js: var pelagosComponentUri = "' . self::$uri . "\";\n"
        );
        $this->component->setJSGlobals();
    }
}
