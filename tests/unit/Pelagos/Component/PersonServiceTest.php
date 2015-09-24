<?php

namespace Pelagos\Component;

/**
 * A test for the Person entity.
 *
 * @runTestsInSeparateProcesses
 *
 * @preserveGlobalState disabled
 */
class PersonServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Property to hold an instance of PersonService.
     *
     * @var \Pelagos\Component\PersonService
     */
    protected $personService;

    /**
     * Property to hold a mock person for testing.
     *
     * @var \Pelagos\Entity\Person
     */
    protected $mockPerson;

    /**
     * Property to hold a mock EntityManager.
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $mockEntityManager;

    /**
     * Property to hold a mock DriverException.
     *
     * @var \Doctrine\DBAL\Driver\DriverException
     */
    protected $mockDriverException;

    /**
     * Property to hold a mock validator.
     *
     * @var mixed
     */
    protected $mockValidator;

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
     * Since this is a unit tests, we mock all dependencies:
     * - \Pelagos\Entity\Person
     * - \Doctrine\ORM\EntityManager
     * - \Pelagos\Persistance
     * - \Doctrine\DBAL\Driver\DriverException
     *
     * @return void
     */
    protected function setUp()
    {
        $this->personService = new \Pelagos\Component\PersonService();

        $this->mockPerson = \Mockery::mock('overload:\Pelagos\Entity\Person');
        $this->mockPerson->shouldReceive('getId')->andReturn(0);
        $this->mockPerson->shouldReceive('getFirstName')->andReturn(self::$firstName);
        $this->mockPerson->shouldReceive('getLastName')->andReturn(self::$lastName);
        $this->mockPerson->shouldReceive('getEmailAddress')->andReturn(self::$emailAddress);

        $this->mockEntityManager = \Mockery::mock('\Doctrine\ORM\EntityManager');
        $this->mockEntityManager->shouldReceive('persist');

        $mockPersistence = \Mockery::mock('alias:\Pelagos\Persistance');
        $mockPersistence->shouldReceive('createEntityManager')->andReturn($this->mockEntityManager);

        $this->mockDriverException = \Mockery::mock('\Doctrine\DBAL\Driver\DriverException');

        $this->mockValidator = \Mockery::mock('\Symfony\Component\Validator\Validator');
    }

    /**
     * Test validating a person with success.
     *
     * @return void
     */
    public function testValidateSuccess()
    {
        $this->mockValidator->shouldReceive('validate')->andReturn(array());
        $person = $this->personService->validate($this->mockPerson, $this->mockValidator);
        $this->assertInstanceOf('\Pelagos\Entity\Person', $person);
    }

    /**
     * Test validating a person with validation failure.
     *
     * @expectedException \Pelagos\Exception\ValidationException
     *
     * @return void
     */
    public function testValidateFailure()
    {
        $this->mockValidator->shouldReceive('validate')->andReturn(array(1));
        $person = $this->personService->validate($this->mockPerson, $this->mockValidator);
    }

    /**
     * Test persisting a person successfully.
     *
     * @return void
     */
    public function testPersistSuccess()
    {
        $this->mockEntityManager->shouldReceive('flush');
        $person = $this->personService->persist($this->mockPerson);
        $this->assertInstanceOf('\Pelagos\Entity\Person', $person);
        $this->assertSame(0, $person->getId());
        $this->assertEquals(self::$firstName, $person->getFirstName());
        $this->assertEquals(self::$lastName, $person->getLastName());
        $this->assertEquals(self::$emailAddress, $person->getEmailAddress());
    }

    /**
     * Test handling of attempting to persist a person with a missing required field.
     *
     * @expectedException \Pelagos\Exception\MissingRequiredFieldPersistenceException
     *
     * @return void
     */
    public function testPersistMissingRequiredField()
    {
        $this->mockEntityManager->shouldReceive('flush')->andThrow(
            '\Doctrine\DBAL\Exception\NotNullConstraintViolationException',
            null,
            $this->mockDriverException
        );
        $person = $this->personService->persist($this->mockPerson);
    }

    /**
     * Test handling of attempting to persist a person that already exists in persistence.
     *
     * @expectedException \Pelagos\Exception\RecordExistsPersistenceException
     *
     * @return void
     */
    public function testPersistRecordExists()
    {
        $this->mockEntityManager->shouldReceive('flush')->andThrow(
            '\Doctrine\DBAL\Exception\UniqueConstraintViolationException',
            null,
            $this->mockDriverException
        );
        $person = $this->personService->persist($this->mockPerson);
    }

    /**
     * Test handling of attempting to persist a person and encountering a persistence error.
     *
     * This tests for handling of persistence errors not handled specifically.
     *
     * @expectedException \Pelagos\Exception\PersistenceException
     *
     * @return void
     */
    public function testPersistPersistenceError()
    {
        $this->mockEntityManager->shouldReceive('flush')->andThrow('\Doctrine\DBAL\DBALException');
        $person = $this->personService->persist($this->mockPerson);
    }

    /**
     * Test getting a person that exists.
     *
     * Should return the person for the provided id.
     *
     * @return void
     */
    public function testGetPerson()
    {
        $this->mockEntityManager->shouldReceive('find')->andReturn($this->mockPerson);
        $person = $this->personService->getPerson(0);
        $this->assertInstanceOf('\Pelagos\Entity\Person', $person);
        $this->assertSame(0, $person->getId());
        $this->assertEquals(self::$firstName, $person->getFirstName());
        $this->assertEquals(self::$lastName, $person->getLastName());
        $this->assertEquals(self::$emailAddress, $person->getEmailAddress());
    }

    /**
     * Test getting a person that exists by passing a string that contains an integer.
     *
     * Should return the person for the provided id.
     *
     * @return void
     */
    public function testGetPersonIntegerString()
    {
        $this->mockEntityManager->shouldReceive('find')->andReturn($this->mockPerson);
        $person = $this->personService->getPerson('0');
        $this->assertInstanceOf('\Pelagos\Entity\Person', $person);
        $this->assertSame(0, $person->getId());
        $this->assertEquals(self::$firstName, $person->getFirstName());
        $this->assertEquals(self::$lastName, $person->getLastName());
        $this->assertEquals(self::$emailAddress, $person->getEmailAddress());
    }

    /**
     * Test handling of attempting to get a person with an invalid id.
     *
     * @expectedException \Pelagos\Exception\ArgumentException
     *
     * @return void
     */
    public function testGetPersonInvalidID()
    {
        $person = $this->personService->getPerson('foo');
    }

    /**
     * Test attempting to get a person with an invalid id and getting back the id sent upon catching the exception.
     *
     * @return void
     */
    public function testGetPersonInvalidIDGetID()
    {
        try {
            $person = $this->personService->getPerson('foo');
        } catch (\Pelagos\Exception\ArgumentException $e) {
            $this->assertEquals('id', $e->getArgumentName());
            $this->assertEquals('foo', $e->getArgumentValue());
        }
    }

    /**
     * Test handling of attempting to get a person that does not exist in persistence.
     *
     * @expectedException \Pelagos\Exception\RecordNotFoundPersistenceException
     *
     * @return void
     */
    public function testGetPersonRecordNotFound()
    {
        $this->mockEntityManager->shouldReceive('find')->andReturnNull();
        $person = $this->personService->getPerson(0);
    }

    /**
     * Test handling of attempting to get a person and encountering a persistence error.
     *
     * This tests for handling of persistence errors not handled specifically.
     *
     * @expectedException \Pelagos\Exception\PersistenceException
     *
     * @return void
     */
    public function testGetPersonPersistenceError()
    {
        $this->mockEntityManager->shouldReceive('find')->andThrow('\Doctrine\DBAL\DBALException');
        $person = $this->personService->getPerson(0);
    }
}
