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
     * Static class variable containing a name to use for testing.
     *
     * @var string $testName
     */
    protected static $testName = 'My Funding Organization';

    /**
     * Static class variable containing an email address to use for testing.
     *
     * @var string $testEmailAddress
     */
    protected static $testEmailAddress = 'griidc@gomri.org';

    /**
     * Static class variable containing a description to use for testing.
     *
     * @var string $testDescription
     */
    protected static $testDescription = 'This is an organization that funds stuff. That is all.';

    /**
     * Static class variable containing a URL to use for testing.
     *
     * @var string $testUrl
     */
    protected static $testUrl = 'http://gulfresearchinitiative.org';

    /**
     * Static class variable containing a phone number to use for testing.
     *
     * @var string $testPhoneNumber
     */
    protected static $testPhoneNumber = '555-555-5555';

    /**
     * Static class variable containing a delivery point to use for testing.
     *
     * @var string $testDeliveryPoint
     */
    protected static $testDeliveryPoint = '6300 Ocean Dr.';

    /**
     * Static class variable containing a city to use for testing.
     *
     * @var string $testCity
     */
    protected static $testCity = 'Corpus Christi';

    /**
     * Static class variable containing an administrative area to use for testing.
     *
     * @var string $testAdministrativeArea
     */
    protected static $testAdministrativeArea = 'Texas';

    /**
     * Static class variable containing a postal code to use for testing.
     *
     * @var string $testPostalCode
     */
    protected static $testPostalCode = '78412';

    /**
     * Static class variable containing a country to use for testing.
     *
     * @var string $testCountry
     */
    protected static $testCountry = 'USA';

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
     * Test attempting to create a FundingOrganization when no user is logged in.
     *
     * Should fail and return 401 with a message indicating that login is required.
     *
     * @return void
     */
    public function testCreateFundingOrganizationNotLoggedIn()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'POST',
                'PATH_INFO' => "/FundingOrganization",
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
     * Test attempting to create a FundingOrganization with no post parameters.
     *
     * Should fail and return 400 with a message indicating that firstName is required.
     *
     * @return void
     */
    public function testCreateFundingOrganizationNoParameters()
    {
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'POST',
                'PATH_INFO' => '/FundingOrganization',
            )
        );
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                400,
                'Cannot create FundingOrganization because: Name is required'
            )
        );
        require 'index.php';
    }

    /**
     * Test attempting to create a FundingOrganization and encountering a not null violation when persisting.
     *
     * Should fail and return 400 with a message indicating that a required field is missing.
     *
     * @return void
     */
    public function testCreateFundingOrganizationEmptyRequiredField()
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
                'PATH_INFO' => '/FundingOrganization',
                'slim.input' => 'name=' . self::$testName
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                400,
                'Cannot create FundingOrganization because a required field is missing.'
            )
        );
        require 'index.php';
    }

    /**
     * Test attempting to create a FundingOrganization and encountering a unique constraint violation when persisting.
     *
     * Should fail and return 409 with a message indicating that the reocord already exists.
     *
     * @return void
     */
    public function testCreateFundingOrganizationRecordExists()
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
                'PATH_INFO' => '/FundingOrganization',
                'slim.input' => 'name=' . self::$testName
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                409,
                'Cannot create FundingOrganization: Unique constraint violation.'
            )
        );
        require 'index.php';
    }

    /**
     * Test attempting to create a FundingOrganization and encountering a database error when persisting.
     *
     * Should fail and return 500 with a message indicating that a database error has occured.
     *
     * @return void
     */
    public function testCreateFundingOrganizationPersistenceError()
    {
        $this->mockEntityManager->shouldReceive('flush')->andThrow(
            '\Doctrine\DBAL\DBALException'
        );
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'POST',
                'PATH_INFO' => '/FundingOrganization',
                'slim.input' => 'name=' . self::$testName
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString($this->makeHTTPStatusJSON(500, 'A database error has occured: '));
        require 'index.php';
    }

    /**
     * Test attempting to create a FundingOrganization and encountering a general error when persisting.
     *
     * Should fail and return 500 with a message indicating that a general error has occured.
     *
     * @return void
     */
    public function testCreateFundingOrganizationGeneralError()
    {
        $this->mockEntityManager->shouldReceive('flush')->andThrow(
            '\Exception'
        );
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'POST',
                'PATH_INFO' => '/FundingOrganization',
                'slim.input' => 'name=' . self::$testName
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString($this->makeHTTPStatusJSON(500, 'A general error has occured: '));
        require 'index.php';
    }

    /**
     * Test that creating a FundingOrganization with all valid parameters is successful.
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
                'PATH_INFO' => '/FundingOrganization',
                'slim.input' => 'name=' . self::$testName
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                201,
                'A FundingOrganization has been successfully created with an ID of 0.',
                array(
                    'id' => null,
                    'creationTimeStamp' => null,
                    'creator' => 'test',
                    'name' => 'My Funding Organization',
                    'emailAddress' => null,
                    'description' => null,
                    'url' => null,
                    'phoneNumber' => null,
                    'deliveryPoint' => null,
                    'city' => null,
                    'administrativeArea' => null,
                    'postalCode' => null,
                    'country' => null,
                    'modificationTimeStamp' => null,
                    'modifier' => 'test',
                )
            )
        );
        require 'index.php';
    }

    /**
     * Test that getting a FundingOrganization with an invalid id.
     *
     * Should return 400 with a message indicating the id must be a non-negative integer.
     *
     * @return void
     */
    public function testGetFundingOrganizationInvalidId()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/FundingOrganization/foo',
            )
        );
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                400,
                'FundingOrganization id must be a non-negative integer'
            )
        );
        require 'index.php';
    }

    /**
     * Test that getting a FundingOrganization that doesn't exist.
     *
     * Should return 404 with a message indicating the FundingOrganization is not found.
     *
     * @return void
     */
    public function testGetFundingOrganizationNotFound()
    {
        $this->mockEntityManager->shouldReceive('find')->andReturnNull();
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/FundingOrganization/0',
            )
        );
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                404,
                'Could not find a FundingOrganization with id: 0'
            )
        );
        require 'index.php';
    }

    /**
     * Test that getting a FundingOrganization and encountering a persistence error.
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
                'PATH_INFO' => '/FundingOrganization/0',
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
     * Test that getting a FundingOrganization and encountering a general error.
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
                'PATH_INFO' => '/FundingOrganization/0',
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
     * Test that getting a FundingOrganization with a valid id is successful.
     *
     * Should return 200 with a message indicating the FundingOrganization was found
     * and a JSON serialization of the FundingOrganization as the data package.
     *
     * @return void
     */
    public function testGetSuccess()
    {
        $fundingOrganizationData = array(
            'id' => null,
            'creationTimeStamp' => null,
            'creator' => self::$testCreator,
            'name' => self::$testName,
            'emailAddress' => self::$testEmailAddress,
            'description' => self::$testDescription,
            'url' => self::$testUrl,
            'phoneNumber' => self::$testPhoneNumber,
            'deliveryPoint' => self::$testDeliveryPoint,
            'city' => self::$testCity,
            'administrativeArea' => self::$testAdministrativeArea,
            'postalCode' => self::$testPostalCode,
            'country' => self::$testCountry,
            'modificationTimeStamp' => null,
            'modifier' => self::$testCreator
        );
        $testFundingOrganization = new \Pelagos\Entity\FundingOrganization;
        $testFundingOrganization->update($fundingOrganizationData);
        $this->mockEntityManager->shouldReceive('find')->andReturn($testFundingOrganization);
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/FundingOrganization/0',
            )
        );
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                200,
                'Found FundingOrganization with id: 0',
                $fundingOrganizationData
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
        $organizationData = array (
            'id' => null,
            'creationTimeStamp' => null,
            'creator' => 'test',
            'name' => self::$testName,
            'emailAddress' => self::$testEmailAddress,
            'description' => self::$testDescription,
            'url' => self::$testUrl,
            'phoneNumber' => self::$testPhoneNumber,
            'deliveryPoint' => self::$testDeliveryPoint,
            'city' => self::$testCity,
            'administrativeArea' => self::$testAdministrativeArea,
            'postalCode' => self::$testPostalCode,
            'country' => self::$testCountry,
            'modificationTimeStamp' => null,
            'modifier' => 'test'

        );
        $fundingOrganization = new \Pelagos\Entity\FundingOrganization;
        $fundingOrganization->update($organizationData);
        $this->mockEntityManager->shouldReceive('find')->andReturn($fundingOrganization);
        $this->mockEntityManager->shouldReceive('flush');
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'PUT',
                'PATH_INFO' => '/FundingOrganization/0',
                'slim.input' => 'name=' . self::$testName
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                200,
                'A FundingOrganization has been successfully updated with an ID of 0.',
                $organizationData
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
