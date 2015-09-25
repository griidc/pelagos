<?php

namespace Pelagos;

if (!function_exists('\Pelagos\drupal_exit')) {
    /**
     * Mock the drupal_exit function to just print drupal_exit followed by a newline.
     *
     * @return void
     */
    function drupal_exit()
    {
        echo "drupal_exit\n";
    }
}

/**
 * A sets of tests for the Person web service.
 *
 * @runTestsInSeparateProcesses
 *
 * @preserveGlobalState disabled
 */
class PersonWebServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Variable to save the current working directory.
     *
     * @var string
     */
    private $saveCwd;

    /**
     * An instance of a mock EntityManager.
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $mockEntityManager;

    /**
     * A valid first name to use for testing.
     *
     * @var string
     */
    protected static $firstName = 'test';

    /**
     * A valid last name to use for testing.
     *
     * @var string
     */
    protected static $lastName = 'user';

    /**
     * A valid email address to use for testing.
     *
     * @var string
     */
    protected static $emailAddress = 'test.user@testdomian.tld';

    /**
     * An invalid email address to use for testing.
     *
     * @var string
     */
    protected static $badEmailAddress = 'bademail@testdomian';

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

        $this->mockEntityManager = \Mockery::mock('\Doctrine\ORM\EntityManager');
        $this->mockEntityManager->shouldReceive('persist');

        $mockPersistence = \Mockery::mock('alias:\Pelagos\Persistance');
        $mockPersistence->shouldReceive('createEntityManager')->andReturn($this->mockEntityManager);

        $this->saveCwd = getcwd();
        chdir(__DIR__ . '/../../../../web/services/person');
    }

    /**
     * Test attempting to create a person when no user is logged in.
     *
     * Should fail and return 401 with a message indicating that login is required.
     *
     * @return void
     */
    public function testCreatePersonNotLoggedIn()
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
    public function testCreatePersonNoParameters()
    {
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        \Slim\Environment::mock(array( 'REQUEST_METHOD' => 'POST'));
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                400,
                'Cannot create person because: First name is required, Last name is required, Email address is required'
            )
        );
        require 'index.php';
    }

    /**
     * Test attempting to create a person with lastName missing from the post parameters.
     *
     * Should fail and return 400 with a message indicating that lastName is required.
     *
     * @return void
     */
    public function testCreatePersonMissingLastName()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'POST',
                'slim.input' => 'firstName=' . self::$firstName .
                                '&emailAddress=' . self::$emailAddress
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                400,
                'Cannot create person because: Last name is required'
            )
        );
        require 'index.php';
    }

    /**
     * Test attempting to create a person with emailAddress missing from the post parameters.
     *
     * Should fail and return 400 with a message indicating that emailAddress is required.
     *
     * @return void
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
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                400,
                'Cannot create person because: Email address is required'
            )
        );
        require 'index.php';
    }

    /**
     * Test attempting to create a person with an invalid emailAddress sent in the post parameters.
     *
     * Should fail and return 400 with a message indicating that emailAddress is improperly formatted.
     *
     * @return void
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
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                400,
                'Cannot create person because: Email address is invalid'
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
    public function testCreatePersonEmptyRequiredField()
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
                'slim.input' => 'firstName=' . self::$firstName .
                                '&lastName=' . self::$lastName .
                                '&emailAddress=' . self::$emailAddress
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                400,
                'Cannot create person because a required field is missing.'
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
    public function testCreatePersonRecordExists()
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
                'slim.input' => 'firstName=' . self::$firstName .
                                '&lastName=' . self::$lastName .
                                '&emailAddress=' . self::$emailAddress
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                409,
                'Cannot create person: Unique constraint violation.'
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
     *
     * Should fail and return 500 with a message indicating that a general error has occured.
     *
     * @return void
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
     *
     * Should return 200 with a message indicating that a person has been successfully created
     * and listing details about the person.
     *
     * @return void
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
                201,
                'A person has been successfully created: ' .
                'test user (test.user@testdomian.tld) with at ID of 0.'
            )
        );
        require 'index.php';
    }

    /**
     * Test that getting a person with an invalid id.
     *
     * Should return 400 with a message indicating the id must be a non-negative integer.
     *
     * @return void
     */
    public function testGetPersonInvalidId()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/foo',
            )
        );
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                400,
                'Person id must be a non-negative integer'
            )
        );
        require 'index.php';
    }

    /**
     * Test that getting a person that doesn't exist.
     *
     * Should return 404 with a message indicating the person is not found.
     *
     * @return void
     */
    public function testGetPersonNotFound()
    {
        $this->mockEntityManager->shouldReceive('find')->andReturnNull();
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/0',
            )
        );
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                404,
                'Could not find a Person with id: 0'
            )
        );
        require 'index.php';
    }

    /**
     * Test that getting a person and encountering a persistence error.
     *
     * Should return 500 with a message indicating what happened.
     *
     * @return void
     */
    public function testGetPersonPersistenceError()
    {
        $this->mockEntityManager->shouldReceive('find')->andThrow(
            '\Doctrine\DBAL\DBALException'
        );
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/0',
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
     * Test that getting a person and encountering a general error.
     *
     * Should return 500 with a message indicating what happened.
     *
     * @return void
     */
    public function testGetPersonGeneralError()
    {
        $this->mockEntityManager->shouldReceive('find')->andThrow(
            '\Exception'
        );
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/0',
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
     * Test that getting a person with a valid id is successful.
     *
     * Should return 200 with a message indicating the person was found
     * and a JSON serialization of the person as the data package.
     *
     * @return void
     */
    public function testGetPersonSuccess()
    {
        $personData = array(
            'id' => 0,
            'firstName' => self::$firstName,
            'lastName' => self::$lastName,
            'emailAddress' => self::$emailAddress,
        );
        $mockPerson = \Mockery::mock('\Pelagos\Entity\Person, JsonSerializable');
        $mockPerson->shouldReceive('jsonSerialize')->andReturn($personData);
        $this->mockEntityManager->shouldReceive('find')->andReturn($mockPerson);
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/0',
            )
        );
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                200,
                'Found Person with id: 0',
                $personData
            )
        );
        require 'index.php';
    }

    /**
     * Test attempting to update a person when no user is logged in.
     *
     * Should fail and return 401 with a message indicating that login is required.
     *
     * @return void
     */
    public function testUpdatePersonNotLoggedIn()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'PUT',
                'PATH_INFO' => '/0',
            )
        );
        $this->expectOutputString($this->makeHTTPStatusJSON(401, 'Login Required to use this feature'));
        require 'index.php';
    }

    /**
     * Test that updating a person with an invalid id.
     *
     * Should return 400 with a message indicating the id must be a non-negative integer.
     *
     * @return void
     */
    public function testUpdatePersonInvalidId()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'PUT',
                'PATH_INFO' => '/foo',
            )
        );
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                400,
                'Person id must be a non-negative integer'
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        require 'index.php';
    }

    /**
     * Test that updating a person that doesn't exist.
     *
     * Should return 404 with a message indicating the person is not found.
     *
     * @return void
     */
    public function testUpdatePersonNotFound()
    {
        $this->mockEntityManager->shouldReceive('find')->andReturnNull();
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'PUT',
                'PATH_INFO' => '/0',
            )
        );
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                404,
                'Could not find a Person with id: 0'
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        require 'index.php';
    }

    /**
     * Test that updating a person and encountering a persistence error.
     *
     * Should return 500 with a message indicating what happened.
     *
     * @return void
     */
    public function testUpdatePersonPersistenceError()
    {
        $this->mockEntityManager->shouldReceive('find')->andThrow(
            '\Doctrine\DBAL\DBALException'
        );
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'PUT',
                'PATH_INFO' => '/0',
            )
        );
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                500,
                'A database error has occured: '
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        require 'index.php';
    }

    /**
     * Test updating a person and encountering a general error.
     *
     * Should return 500 with a message indicating what happened.
     *
     * @return void
     */
    public function testUpdatePersonGeneralError()
    {
        $this->mockEntityManager->shouldReceive('find')->andThrow(
            '\Exception'
        );
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'PUT',
                'PATH_INFO' => '/0',
            )
        );
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                500,
                'A general error has occured: '
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        require 'index.php';
    }

    /**
     * Test that updating a person with a valid id is successful.
     *
     * Should return 200 with a message indicating the person was updated
     * and a JSON serialization of the person as the data package.
     *
     * @return void
     */
    public function testUpdatePersonSuccess()
    {
        $personData = array(
            'id' => null,
            'creator' => 'test',
            'creationTimeStamp' => null,
            'modifier' => 'test',
            'modificationTimeStamp' => null,
            'firstName' => self::$firstName,
            'lastName' => self::$lastName,
            'emailAddress' => self::$emailAddress,
        );
        $person = new \Pelagos\Entity\Person;
        $person->update($personData);
        $this->mockEntityManager->shouldReceive('find')->andReturn($person);
        $this->mockEntityManager->shouldReceive('flush');
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'PUT',
                'PATH_INFO' => '/0',
                'slim.input' => 'firstName=' . self::$firstName .
                                '&lastName=' . self::$lastName .
                                '&emailAddress=' . self::$emailAddress
            )
        );
        $this->expectOutputString(
            $this->makeHTTPStatusJSON(
                200,
                'Updated Person with id: 0',
                $personData
            )
        );
        $GLOBALS['user'] = new \Pelagos\Tests\Helpers\TestUser;
        require 'index.php';
    }

    /**
     * Test of setting the property of firstName to a valid value.
     *
     * @return void
     */
    public function testValidatePropertyFirstName()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/validateProperty/',
                'QUERY_STRING' => 'firstName=' . self::$firstName
            )
        );
        $this->expectOutputString(json_encode(true) . "drupal_exit\n");
        require 'index.php';
    }

    /**
     * Test of setting the property of lastName to a valid value.
     *
     * @return void
     */
    public function testValidatePropertyLastName()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/validateProperty/',
                'QUERY_STRING' => 'lastName=' . self::$firstName
            )
        );
        $this->expectOutputString(json_encode(true) . "drupal_exit\n");
        require 'index.php';
    }

    /**
     * Test of setting the property of emailAddress to a valid value.
     *
     * @return void
     */
    public function testValidatePropertyEmailAddress()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/validateProperty/',
                'QUERY_STRING' => 'emailAddress=' . self::$emailAddress
            )
        );
        $this->expectOutputString(json_encode(true) . "drupal_exit\n");
        require 'index.php';
    }

    /**
     * Test of setting the property of firstName to blank.
     *
     * @return void
     */
    public function testValidatePropertyFirstNameBlank()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/validateProperty/',
                'QUERY_STRING' => 'firstName='
            )
        );
        $this->expectOutputString(json_encode('First name is required') . "drupal_exit\n");
        require 'index.php';
    }

    /**
     * Test of setting the property of lastName to blank.
     *
     * @return void
     */
    public function testValidatePropertyLastNameBlank()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/validateProperty/',
                'QUERY_STRING' => 'lastName='
            )
        );
        $this->expectOutputString(json_encode('Last name is required') . "drupal_exit\n");
        require 'index.php';
    }

    /**
     * Test of setting emailAddress to blank.
     *
     * @return void
     */
    public function testValidatePropertyEmailAddressBlank()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/validateProperty/',
                'QUERY_STRING' => 'emailAddress='
            )
        );
        $this->expectOutputString(json_encode('Email address is required') . "drupal_exit\n");
        require 'index.php';
    }

    /**
     * Test of setting the property of firstName to a string with invalid characters.
     *
     * @return void
     */
    public function testValidatePropertyFirstNameBad()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/validateProperty/',
                'QUERY_STRING' => 'firstName=Bad<i>Name</i>'
            )
        );
        $this->expectOutputString(json_encode('First name cannot contain angle brackets (< or >)') . "drupal_exit\n");
        require 'index.php';
    }

    /**
     * Test of setting the property of lastName to a string with invalid characters.
     *
     * @return void
     */
    public function testValidatePropertyLastNameBad()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/validateProperty/',
                'QUERY_STRING' => 'lastName=Bad<i>Name</i>'
            )
        );
        $this->expectOutputString(json_encode('Last name cannot contain angle brackets (< or >)') . "drupal_exit\n");
        require 'index.php';
    }

    /**
     * Test of setting the property emailAddress to a string of the wrong syntax.
     *
     * @return void
     */
    public function testValidatePropertyEmailAddressBad()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/validateProperty/',
                'QUERY_STRING' => 'emailAddress=bad.address@missing-tld'
            )
        );
        $this->expectOutputString(json_encode('Email address is invalid') . "drupal_exit\n");
        require 'index.php';
    }

    /**
     * Test of attempting to validate an unknown field.
     *
     * @return void
     */
    public function testValidatePropertyUnknown()
    {
        $propName = 'unknownProperty';
        $propVal = 'someValue';
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/validateProperty/',
                'QUERY_STRING' => "$propName=$propVal"
            )
        );
        $this->expectOutputString(
            json_encode("The parameter $propName is not a valid property of Person.") . "drupal_exit\n"
        );
        require 'index.php';
    }

    /**
     * Test attempting to validate more than one property in the same request.
     *
     * @return void
     */
    public function testValidateMultipleProperties()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/validateProperty/',
                'QUERY_STRING' => self::$firstName . '&' . self::$lastName
            )
        );
        $this->expectOutputString(json_encode('Validation of multiple properties not allowed.') . "drupal_exit\n");
        require 'index.php';
    }

    /**
     * Test of validating "nothing".
     *
     * @return void
     */
    public function testValidateNothing()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/validateProperty/'
            )
        );
        $this->expectOutputString(json_encode('Property to be validated not supplied') . "drupal_exit\n");
        require 'index.php';
    }

    /**
     * Utility method to build a JSON string equivalent to a JSON serialized HTTPStatus.
     *
     * @param integer $code    The HTTP status code.
     * @param string  $message The HTTP status message.
     * @param mixed   $data    The HTTP data package.
     *
     * @return string A JSON string containing $code, $message, and $data.
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
    public function testPersonDefaultRoute()
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
