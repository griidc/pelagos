<?php

namespace Pelagos;

/**
 * Integration tests for funding organization web service.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class EntityWebServiceTest extends \PHPUnit_Framework_TestCase
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
     * Static class variable containing an ID to use for testing.
     *
     * @var string $testId
     */
    protected static $testId = '0';

    /**
     * Static class variable containing username to use as creator.
     *
     * @var string $testCreator
     */
    protected static $testCreator = 'testcreator';

    /**
     * Static class variable containing username to use as modifier.
     *
     * @var string $testModifier
     */
    protected static $testModifier = 'testmodifier';

    /**
     * Static class variable containing a name to use for testing.
     *
     * @var string $testName
     */
    protected static $testName = 'My Entity';

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
        chdir(__DIR__ . '/../../../../web/services/entity');
    }

    /**
     * Test attempting to create a ConcreteEntity when no user is logged in.
     *
     * Should fail and return 401 with a message indicating that login is required.
     *
     * @return void
     */
    public function testCreateNotLoggedIn()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'POST',
                'PATH_INFO' => '/ConcreteEntity',
            )
        );
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                401,
                'Login Required to use this feature'
            )
        );
        require 'index.php';
    }

    /**
     * Test attempting to create a ConcreteEntity with no post parameters.
     *
     * Should fail and return 400 with a message indicating that firstName is required.
     *
     * @return void
     */
    public function testCreateNoParameters()
    {
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'POST',
                'PATH_INFO' => '/ConcreteEntity',
            )
        );
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                400,
                'Cannot create ConcreteEntity because: Name is required'
            )
        );
        require 'index.php';
    }

    /**
     * Test attempting to create a ConcreteEntity and encountering a not null violation when persisting.
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
                'PATH_INFO' => '/ConcreteEntity',
                'slim.input' => 'name=' . self::$testName
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                400,
                'Cannot create ConcreteEntity because a required field is missing.'
            )
        );
        require 'index.php';
    }

    /**
     * Test attempting to create a ConcreteEntity and encountering a unique constraint violation when persisting.
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
                'PATH_INFO' => '/ConcreteEntity',
                'slim.input' => 'name=' . self::$testName
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                409,
                'Cannot create ConcreteEntity: Unique constraint violation.'
            )
        );
        require 'index.php';
    }

    /**
     * Test attempting to create a ConcreteEntity and encountering a database error when persisting.
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
                'PATH_INFO' => '/ConcreteEntity',
                'slim.input' => 'name=' . self::$testName
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString($this->makeHTTPStatusJSON(500, 'A database error has occured: '));
        require 'index.php';
    }

    /**
     * Test attempting to create a ConcreteEntity and encountering a general error when persisting.
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
                'PATH_INFO' => '/ConcreteEntity',
                'slim.input' => 'name=' . self::$testName
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString($this->makeHTTPStatusJSON(500, 'A general error has occured: '));
        require 'index.php';
    }

    /**
     * Test that creating a ConcreteEntity with all valid parameters is successful.
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
                'PATH_INFO' => '/ConcreteEntity',
                'slim.input' => 'name=' . self::$testName
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                201,
                'A ConcreteEntity has been successfully created with an ID of 0.',
                array(
                    'id' => null,
                    'creator' => 'test',
                    'creationTimeStamp' => null,
                    'modifier' => 'test',
                    'modificationTimeStamp' => null,
                )
            )
        );
        require 'index.php';
    }

    /**
     * Test that getting a ConcreteEntity with an invalid id.
     *
     * Should return 400 with a message indicating the id must be a non-negative integer.
     *
     * @return void
     */
    public function testGetInvalidId()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/ConcreteEntity/foo',
            )
        );
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                400,
                'ConcreteEntity id must be a non-negative integer'
            )
        );
        require 'index.php';
    }

    /**
     * Test that getting a ConcreteEntity that doesn't exist.
     *
     * Should return 404 with a message indicating the ConcreteEntity is not found.
     *
     * @return void
     */
    public function testGetNotFound()
    {
        $this->mockEntityManager->shouldReceive('find')->andReturnNull();
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/ConcreteEntity/0',
            )
        );
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                404,
                'Could not find a ConcreteEntity with id: 0'
            )
        );
        require 'index.php';
    }

    /**
     * Test that getting a ConcreteEntity and encountering a persistence error.
     *
     * Should return 500 with a message indicating what happened.
     *
     * @return void
     */
    public function testGetPersistenceError()
    {
        $this->mockEntityManager->shouldReceive('find')->andThrow(
            '\Doctrine\DBAL\DBALException'
        );
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/ConcreteEntity/0',
            )
        );
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                500,
                'A database error has occured: '
            )
        );
        require 'index.php';
    }

    /**
     * Test that getting a ConcreteEntity and encountering a general error.
     *
     * Should return 500 with a message indicating what happened.
     *
     * @return void
     */
    public function testGetGeneralError()
    {
        $this->mockEntityManager->shouldReceive('find')->andThrow(
            '\Exception'
        );
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/ConcreteEntity/0',
            )
        );
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                500,
                'A general error has occured: '
            )
        );
        require 'index.php';
    }

    /**
     * Test that getting a ConcreteEntity with a valid id is successful.
     *
     * Should return 200 with a message indicating the ConcreteEntity was found
     * and a JSON serialization of the ConcreteEntity as the data package.
     *
     * @return void
     */
    public function testGetSuccess()
    {
        $concreteEntityData = array(
            'id' => null,
            'creator' => self::$testCreator,
            'creationTimeStamp' => null,
            'modifier' => self::$testCreator,
            'modificationTimeStamp' => null,
        );
        $testConcreteEntity = new \Pelagos\Entity\ConcreteEntity;
        $testConcreteEntity->update($concreteEntityData);
        $this->mockEntityManager->shouldReceive('find')->andReturn($testConcreteEntity);
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/ConcreteEntity/0',
            )
        );
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                200,
                'Found ConcreteEntity with id: 0',
                $concreteEntityData
            )
        );
        require 'index.php';
    }

    /**
     * Test Successful Update.
     *
     * Should return 200 with a message indicating that a funding organization has been successfully updated
     * and listing details about the funding organization including this changed field.
     *
     * @return void
     */
    public function testUpdateSuccess()
    {
        $concreteEntityData = array (
            'id' => null,
            'creator' => 'test',
            'creationTimeStamp' => null,
            'modifier' => 'test',
            'modificationTimeStamp' => null,
            'name' => self::$testName,
        );
        $concreteEntity = new \Pelagos\Entity\ConcreteEntity;
        $concreteEntity->update($concreteEntityData);
        $this->mockEntityManager->shouldReceive('find')->andReturn($concreteEntity);
        $this->mockEntityManager->shouldReceive('flush');
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'PUT',
                'PATH_INFO' => '/ConcreteEntity/0',
                'slim.input' => 'name=' . self::$testName
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        // Unset name because ConcreteEntity does not override jsonSerialize
        unset($concreteEntityData['name']);
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                200,
                'A ConcreteEntity has been successfully updated with an ID of 0.',
                $concreteEntityData
            )
        );
        require 'index.php';
    }

    /**
     * Test successful property validation.
     *
     * Should return a json encoded true.
     *
     * @return void
     */
    public function testValidatePropertyValid()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/ConcreteEntity/validateProperty',
                'QUERY_STRING' => 'name=' . self::$testName,
            )
        );
        $this->expectOutputString(
            json_encode(true) . "drupal_exit\n"
        );
        require 'index.php';
    }

    /**
     * Test unsuccessful property validation.
     *
     * Should return a json encoded string containing why validation failed.
     *
     * @return void
     */
    public function testValidatePropertyInValid()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/ConcreteEntity/validateProperty',
                'QUERY_STRING' => 'name='
            )
        );
        $this->expectOutputString(
            json_encode('Name is required') . "drupal_exit\n"
        );
        require 'index.php';
    }

    /**
     * Test property validation with no parameters.
     *
     * Should return the json encoded string" "Property to be validated not supplied".
     *
     * @return void
     */
    public function testValidatePropertyNoParameters()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/ConcreteEntity/validateProperty',
            )
        );
        $this->expectOutputString(
            json_encode('Property to be validated not supplied') . "drupal_exit\n"
        );
        require 'index.php';
    }

    /**
     * Test property validation with multiple parameters.
     *
     * Should return the json encoded string: "Validation of multiple properties not allowed.".
     *
     * @return void
     */
    public function testValidatePropertyMultipleParameters()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/ConcreteEntity/validateProperty',
                'QUERY_STRING' => 'name=' . self::$testName . '&foo=bar',
            )
        );
        $this->expectOutputString(
            json_encode('Validation of multiple properties not allowed.') . "drupal_exit\n"
        );
        require 'index.php';
    }

    /**
     * Test property validation with a bad parameter.
     *
     * Should return a json encoded string "The parameter $paramName is not a valid property of $entityType.".
     *
     * @return void
     */
    public function testValidatePropertyBadParameter()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/ConcreteEntity/validateProperty',
                'QUERY_STRING' => 'foo=bar'
            )
        );
        $this->expectOutputString(
            json_encode('The parameter foo is not a valid property of ConcreteEntity.') . "drupal_exit\n"
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
