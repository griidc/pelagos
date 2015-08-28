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
     * Should fail and return 400 with a message indicating that firstName is required.
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
     * Should fail and return 400 with a message indicating that lastName is required.
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
     * Should fail and return 400 with a message indicating that a required field is missing.
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
     * Should fail and return 409 with a message indicating that the reocord already exists.
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
                201,
                'A person has been successfully created: ' .
                'test user (test.user@testdomian.tld) with at ID of 0.'
            )
        );
        require 'index.php';
    }

    /**
     * Test that getting a person with an invalid id.
     * Should return 400 with a message indicating the id must be a non-negative integer.
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
     * Should return 404 with a message indicating the person is not found.
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
     * Should return 500 with a message indicating what happened.
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
     * Should return 500 with a message indicating what happened.
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
     * Should return 200 with a message indicating the person was found
     * and a JSON serialization of the person as the data package.
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
     * Should fail and return 401 with a message indicating that login is required.
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
     * Should return 400 with a message indicating the id must be a non-negative integer.
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
     * Should return 404 with a message indicating the person is not found.
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
     * Should return 500 with a message indicating what happened.
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
     * Should return 500 with a message indicating what happened.
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
     * Should return 200 with a message indicating the person was updated
     * and a JSON serialization of the person as the data package.
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
     * Test of setting the property of firstName to a valid value
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
        $this->expectOutputString(json_encode(true)."drupal_exit\n");
        require 'index.php';
    }

    /**
     * Test of setting the property of lastName to a valid value
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
        $this->expectOutputString(json_encode(true)."drupal_exit\n");
        require 'index.php';
    }


    /**
     * Test of setting the property of emailAddress to a valid value
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
        $this->expectOutputString(json_encode(true)."drupal_exit\n");
        require 'index.php';
    }

    /**
     * Test of setting the property of firstName to blank
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
        $this->expectOutputString(json_encode('First name is required')."drupal_exit\n");
        require 'index.php';
    }

    /**
     * Test of setting the property of lastName to blank
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
        $this->expectOutputString(json_encode('Last name is required')."drupal_exit\n");
        require 'index.php';
    }

    /**
     * Test of setting emailAddress to blank
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
        $this->expectOutputString(json_encode('Email address is required')."drupal_exit\n");
        require 'index.php';
    }

    /**
     * Test of setting the property of firstName to a string with invalid characters
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
        $this->expectOutputString(json_encode('First name cannot contain angle brackets (< or >)')."drupal_exit\n");
        require 'index.php';
    }

    /**
     * Test of setting the property of lastName to a string with invalid characters
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
        $this->expectOutputString(json_encode('Last name cannot contain angle brackets (< or >)')."drupal_exit\n");
        require 'index.php';
    }

    /**
     * Test of setting the property emailAddress to a string of the wrong syntax
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
        $this->expectOutputString(json_encode('Email address is invalid')."drupal_exit\n");
        require 'index.php';
    }

    /**
     * Test of attempting to validate an unknown field
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
        $this->expectOutputString(json_encode("The parameter $propName is not a valid property of Person.")."drupal_exit\n");
        require 'index.php';
    }

    /**
     * Test attempting to validate more than one property in the same request
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
        $this->expectOutputString(json_encode('Validation of multiple properties not allowed.')."drupal_exit\n");
        require 'index.php';
    }

    /**
     * Test of validating "nothing"
     */
    public function testValidateNothing()
    {
        \Slim\Environment::mock(
            array(
                'REQUEST_METHOD' => 'GET',
                'PATH_INFO' => '/validateProperty/'
            )
        );
        $this->expectOutputString(json_encode('Property to be validated not supplied')."drupal_exit\n");
        require 'index.php';
    }


    /**
     * Utility method to build a JSON string equivalent to a JSON serialized HTTPStatus.
     *
     * @param int $code The HTTP status code.
     * @param string $message The HTTP status message.
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
     * Test of default route
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
     * Restores current working directory.
     */
    public function tearDown()
    {
        chdir($this->saveCwd);
    }
}
