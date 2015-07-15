<?php

namespace Pelagos;

/**
 * Integration tests for funding organization web service.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class FundingOrganizationWebServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Variable to save the current working directory.
     *
     * @var string $saveCwd
     */
    private $saveCwd;

    /**
     * An instance of a mock EntityManager.
     *
     * @var \Doctrine\ORM\EntityManager $mockEntityManager
     */
    protected $mockEntityManager;

    /**
     * A valid name to use for testing.
     *
     * @var string $name
     */
    protected static $name = 'My Funding Organization';

    /**
     * Set up for tests.
     *
     * We mock \Doctrine\ORM\EntityManager and \Pelagos\Persistance so we don't need a real database.
     * We mock \Doctrine\DBAL\Driver\DriverException so that we can test throwing Doctrine DBAL exceptions.
     *
     * @return void
     */
    public function setUp()
    {
        require_once __DIR__ . '/../../../helpers/TestUser.php';

        $this->mockEntityRepository = \Mockery::mock('\Doctrine\ORM\EntityRepository');
        $this->mockEntityRepository->shouldReceive('findBy')->andReturn(array());

        $this->mockEntityManager = \Mockery::mock('\Doctrine\ORM\EntityManager');
        $this->mockEntityManager->shouldReceive('persist');
        $this->mockEntityManager->shouldReceive('getRepository')->andReturn($this->mockEntityRepository);

        $mockPersistence = \Mockery::mock('alias:\Pelagos\Persistance');
        $mockPersistence->shouldReceive('createEntityManager')->andReturn($this->mockEntityManager);

        $this->saveCwd = getcwd();
        chdir(__DIR__ . '/../../../../web/services/fundingOrganization');
    }

    /**
     * Test attempting to create a person when no user is logged in.
     *
     * Should fail and return 401 with a message indicating that login is required.
     *
     * @return void
     */
    public function testCreateFundingOrganizationNotLoggedIn()
    {
        \Slim\Environment::mock(array('REQUEST_METHOD' => 'POST'));
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                401,
                'Login Required to use this feature'
            )
        );
        require 'index.php';
    }

    /**
     * Test attempting to create a person with no post parameters.
     *
     * Should fail and return 400 with a message indicating that firstName is required.
     *
     * @return void
     */
    public function testCreateNoParameters()
    {
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        \Slim\Environment::mock(array( 'REQUEST_METHOD' => 'POST'));
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                400,
                'Cannot create funding organization because: Name is required'
            )
        );
        require 'index.php';
    }

    /**
     * Test attempting to create a person and encountering a not null violation when persisting.
     *
     * Should fail and return 400 with a message indicating that a required field is missing.
     *
     * @return void
     */
    public function testCreateEmptyRequiredField()
    {
        $pdoException = new \PDOException;
        $pdoException->errorInfo = array('12345', 0, 'ERROR: Not null constraint violation.');
        $this->mockEntityManager->shouldReceive('flush')->andThrow(
            '\Doctrine\DBAL\Exception\NotNullConstraintViolationException',
            null,
            new \Doctrine\DBAL\Driver\PDOException($pdoException)
        );
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'POST',
                'slim.input' => 'name=' . self::$name
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                400,
                'Cannot create funding organization because a required field is missing.'
            )
        );
        require 'index.php';
    }

    /**
     * Test attempting to create a person and encountering a unique constraint violation when persisting.
     *
     * Should fail and return 409 with a message indicating that the reocord already exists.
     *
     * @return void
     */
    public function testCreateRecordExists()
    {
        $pdoException = new \PDOException;
        $pdoException->errorInfo = array('12345', 0, 'ERROR: Unique constraint violation.');
        $this->mockEntityManager->shouldReceive('flush')->andThrow(
            '\Doctrine\DBAL\Exception\UniqueConstraintViolationException',
            null,
            new \Doctrine\DBAL\Driver\PDOException($pdoException)
        );
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'POST',
                'slim.input' => 'name=' . self::$name
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                409,
                'Cannot create funding organization: Unique constraint violation.'
            )
        );
        require 'index.php';
    }

    /**
     * Test attempting to create a person and encountering a database error when persisting.
     *
     * Should fail and return 500 with a message indicating that a database error has occured.
     *
     * @return void
     */
    public function testCreatePersistenceError()
    {
        $this->mockEntityManager->shouldReceive('flush')->andThrow(
            '\Doctrine\DBAL\DBALException'
        );
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'POST',
                'slim.input' => 'name=' . self::$name
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString($this->makeHTTPStatusJSON(500, 'A database error has occured: '));
        require 'index.php';
    }

    /**
     * Test attempting to create a person and encountering a general error when persisting.
     *
     * Should fail and return 500 with a message indicating that a general error has occured.
     *
     * @return void
     */
    public function testCreateGeneralError()
    {
        $this->mockEntityManager->shouldReceive('flush')->andThrow(
            '\Exception'
        );
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'POST',
                'slim.input' => 'name=' . self::$name
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString($this->makeHTTPStatusJSON(500, 'A general error has occured: '));
        require 'index.php';
    }

    /**
     * Test that creating a person with all valid parameters is successful.
     *
     * Should return 200 with a message indicating that a funding organization has been successfully created
     * and listing details about the funding organization.
     *
     * @return void
     */
    public function testCreateSuccess()
    {
        $this->mockEntityManager->shouldReceive('flush');
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'POST',
                'slim.input' => 'name=' . self::$name
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                201,
                'A funding organization has been successfully created: ' . self::$name .
                ' with at ID of 0.'
            )
        );
        require 'index.php';
    }

    /**
     * Utility method to build a JSON string equivalent to a JSON serialized HTTPStatus.
     *
     * @param integer $code    The HTTP status code.
     * @param string  $message The HTTP status message.
     * @param mixed   $data    Data to send along with the response.
     *
     * @return string A JSON string containing $code and $message.
     */
    protected function makeHTTPStatusJSON($code, $message, $data = null)
    {
        $json = "{\"code\":$code,\"message\":\"$message\"";
        if (isset($data)) {
            $json .= ',"data":' . json_encode($data);
        }
        $json .= "}drupal_exit\n";
        return $json;
    }

    /**
     * Test of default route.
     *
     * @return void
     */
    public function testDefaultRoute()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/'
            )
        );
        $this->expectOutputRegex('/This Web service establishes a REST API/');
        require 'index.php';
    }

    /**
     * Tear down method to be run after each test.
     *
     * Restores current working directory.
     *
     * @return void
     */
    public function tearDown()
    {
        chdir($this->saveCwd);
    }
}
