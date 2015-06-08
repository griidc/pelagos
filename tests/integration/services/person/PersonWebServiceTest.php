<?php

namespace Pelagos;

/**
  * Mock the drupal_exit function to just print drupal_exit followed by a newline.
  */
if (!function_exists('\Pelagos\drupal_exit')) {
    function drupal_exit()
    {
        print "drupal_exit\n";
    }
}

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class PersonWebServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var string $saveCwd Variable to save the current working directory. **/
    private $saveCwd;

    /** @var \Doctrine\ORM\EntityManager $mockEntityManager An instance of a mock EntityManager. **/
    protected $mockEntityManager;

    /** @var \Doctrine\DBAL\Driver\DriverException $mockDriverException An instance of a mock DriverException.  **/
    protected $mockDriverException;

    /** @var string $firstName A valid first name to use for testing. **/
    protected static $firstName = 'test';

    /** @var string $lastName A valid last name to use for testing. **/
    protected static $lastName = 'user';

    /** @var string $emailAddress A valid email address to use for testing. **/
    protected static $emailAddress = 'test.user@testdomian.tld';

    /** @var string $emailAddress An invalid email address to use for testing. **/
    protected static $badEmailAddress = 'bademail@testdomian';

    /**
     * Set up for tests.
     * We mock \Doctrine\ORM\EntityManager and \Pelagos\Persistance so we don't need a real database.
     * We mock \Doctrine\DBAL\Driver\DriverException so that we can test throwing Doctrine DBAL exceptions.
     */
    public function setUp()
    {
        require_once __DIR__ . '/../../../helpers/TestUser.php';

        $this->mockEntityManager = \Mockery::mock('\Doctrine\ORM\EntityManager');
        $this->mockEntityManager->shouldReceive('persist');

        $mockPersistence = \Mockery::mock('alias:\Pelagos\Persistance');
        $mockPersistence->shouldReceive('createEntityManager')->andReturn($this->mockEntityManager);

        $this->mockDriverException = \Mockery::mock('\Doctrine\DBAL\Driver\DriverException');

        $this->saveCwd = getcwd();
        chdir(__DIR__ . '/../../../../web/services/person');
    }

    /**
     * Test attempting to create a person when no user is logged in.
     * Should fail and return 401 with a message indicating that login is required.
     */
    public function testCreatePersonNotLoggedIn()
    {
        \Slim\Environment::mock(array('REQUEST_METHOD' => 'POST'));
        $this->expectOutputString($this->makeHTTPStatusJSON(401, 'Login Required to use this feature'));
        require 'index.php';
    }

    /**
     * Test attempting to create a person with no post parameters.
     * Should fail and return 400 with a message indicating that firstName is required.
     */
    public function testCreatePersonNoParameters()
    {
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        \Slim\Environment::mock(array( 'REQUEST_METHOD' => 'POST'));
        $this->expectOutputString($this->makeHTTPStatusJSON(400, 'firstName is required'));
        require 'index.php';
    }

    /**
     * Test attempting to create a person with lastName missing from the post parameters.
     * Should fail and return 400 with a message indicating that lastName is required.
     */
    public function testCreatePersonMissingLastName()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'POST',
                'slim.input' => 'firstName=' . self::$firstName
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString($this->makeHTTPStatusJSON(400, 'lastName is required'));
        require 'index.php';
    }

    /**
     * Test attempting to create a person with emailAddress missing from the post parameters.
     * Should fail and return 400 with a message indicating that emailAddress is required.
     */
    public function testCreatePersonMissingEmailAddress()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'POST',
                'slim.input' => 'firstName=' . self::$firstName .
                                '&lastName=' . self::$lastName
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString($this->makeHTTPStatusJSON(400, 'emailAddress is required'));
        require 'index.php';
    }

    /**
     * Test attempting to create a person with an invalid emailAddress sent in the post parameters.
     * Should fail and return 400 with a message indicating that emailAddress is improperly formatted.
     */
    public function testCreatePersonInvalidEmailAddress()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'POST',
                'slim.input' => 'firstName=' . self::$firstName .
                                '&lastName=' . self::$lastName .
                                '&emailAddress=' . self::$badEmailAddress
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString($this->makeHTTPStatusJSON(400, 'emailAddress is improperly formatted'));
        require 'index.php';
    }

    /**
     * Test attempting to create a person and encountering a not null violation when persisting.
     * Should fail and return 400 with a message indicating that a required field is missing.
     */
    public function testCreatePersonEmptyRequiredField()
    {
        $this->mockEntityManager->shouldReceive('flush')->andThrow(
            '\Doctrine\DBAL\Exception\NotNullConstraintViolationException',
            null,
            $this->mockDriverException
        );
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'POST',
                'slim.input' => 'firstName=' . self::$firstName .
                                '&lastName=' . self::$lastName .
                                '&emailAddress=' . self::$emailAddress
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString($this->makeHTTPStatusJSON(400, 'A required field is missing: '));
        require 'index.php';
    }

    /**
     * Test attempting to create a person and encountering a unique constraint violation when persisting.
     * Should fail and return 409 with a message indicating that the reocord already exists.
     */
    public function testCreatePersonRecordExists()
    {
        $this->mockEntityManager->shouldReceive('flush')->andThrow(
            '\Doctrine\DBAL\Exception\UniqueConstraintViolationException',
            null,
            $this->mockDriverException
        );
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'POST',
                'slim.input' => 'firstName=' . self::$firstName .
                                '&lastName=' . self::$lastName .
                                '&emailAddress=' . self::$emailAddress
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString($this->makeHTTPStatusJSON(409, 'This record already exists.'));
        require 'index.php';
    }

    /**
     * Test attempting to create a person and encountering a database error when persisting.
     * Should fail and return 500 with a message indicating that a database error has occured.
     */
    public function testCreatePersonPersistenceError()
    {
        $this->mockEntityManager->shouldReceive('flush')->andThrow(
            '\Doctrine\DBAL\DBALException'
        );
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'POST',
                'slim.input' => 'firstName=' . self::$firstName .
                                '&lastName=' . self::$lastName .
                                '&emailAddress=' . self::$emailAddress
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString($this->makeHTTPStatusJSON(500, 'A database error has occured: '));
        require 'index.php';
    }

    /**
     * Test attempting to create a person and encountering a general error when persisting.
     * Should fail and return 500 with a message indicating that a general error has occured.
     */
    public function testCreatePersonGeneralError()
    {
        $this->mockEntityManager->shouldReceive('flush')->andThrow(
            '\Exception'
        );
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'POST',
                'slim.input' => 'firstName=' . self::$firstName .
                                '&lastName=' . self::$lastName .
                                '&emailAddress=' . self::$emailAddress
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString($this->makeHTTPStatusJSON(500, 'A general error has occured: '));
        require 'index.php';
    }

    /**
     * Test that creating a person with all valid parameters is successful.
     * Should return 200 with a message indicating that a person has been successfully created
     * and lsiting details about the person.
     */
    public function testCreatePersonSuccess()
    {
        $this->mockEntityManager->shouldReceive('flush');
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'POST',
                'slim.input' => 'firstName=' . self::$firstName .
                                '&lastName=' . self::$lastName .
                                '&emailAddress=' . self::$emailAddress
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                200,
                'A person has been successfully created: ' .
                'test user (test.user@testdomian.tld) with at ID of 0.'
            )
        );
        require 'index.php';
    }

    /**
     * Utility method to build a JSON string equivalent to a JSON serialized HTTPStatus.
     *
     * @param int $code The HTTP status code.
     * @param string $message The HTTP status message.
     * @return string A JSON string containing $code and $message.
     */
    protected function makeHTTPStatusJSON($code, $message)
    {
        return "{\"code\":$code,\"message\":\"$message\"}drupal_exit\n";
    }

    /**
     * Tear down method to be run after each test.
     * Restores current working directory.
     */
    public function tearDown()
    {
        chdir($this->saveCwd);
    }
}
