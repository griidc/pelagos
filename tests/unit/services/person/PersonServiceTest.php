<?php

namespace Pelagos\Component;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class PersonServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Pelagos\Component\PersonService $personService ... **/
    protected $personService;

    /** @var \Doctrine\ORM\EntityManager $mockEntityManager ... **/
    protected $mockEntityManager;

    /** @var \Doctrine\DBAL\Driver\DriverException $mockDriverException ... **/
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
     * Since this is a unit tests, we mock all dependencies:
     * - \Pelagos\Entity\Person
     * - \Doctrine\ORM\EntityManager
     * - \Pelagos\Persistance
     * - \Doctrine\DBAL\Driver\DriverException
     */
    protected function setUp()
    {
        require_once __DIR__ . '/../../../../web/services/person/lib/PersonService.php';
        $this->personService = new \Pelagos\Component\PersonService();

        $mockPerson = \Mockery::mock('overload:\Pelagos\Entity\Person');
        $mockPerson->shouldReceive('getId')->andReturn(0);
        $mockPerson->shouldReceive('getFirstName')->andReturn(self::$firstName);
        $mockPerson->shouldReceive('getLastName')->andReturn(self::$lastName);
        $mockPerson->shouldReceive('getEmailAddress')->andReturn(self::$emailAddress);

        $this->mockEntityManager = \Mockery::mock('\Doctrine\ORM\EntityManager');
        $this->mockEntityManager->shouldReceive('persist');

        $mockPersistence = \Mockery::mock('alias:\Pelagos\Persistance');
        $mockPersistence->shouldReceive('createEntityManager')->andReturn($this->mockEntityManager);

        $this->mockDriverException = \Mockery::mock('\Doctrine\DBAL\Driver\DriverException');
    }

    /**
     * Test creating a person with valid required parameters.
     * Should successfully create a person and return it.
     */
    public function testCreatePerson()
    {
        $this->mockEntityManager->shouldReceive('flush');
        $person = $this->personService->createPerson(self::$firstName, self::$lastName, self::$emailAddress);
        $this->assertInstanceOf('\Pelagos\Entity\Person', $person);
        $this->assertSame(0, $person->getId());
        $this->assertEquals(self::$firstName, $person->getFirstName());
        $this->assertEquals(self::$lastName, $person->getLastName());
        $this->assertEquals(self::$emailAddress, $person->getEmailAddress());
    }

    /**
     * Test handling of attempting to persist a person with a missing required field.
     * This should never happen as the Peron constructor checks for all required fields.
     *
     * @expectedException \Pelagos\Exception\MissingRequiredFieldPersistenceException
     */
    public function testCreatePersonMissingRequiredField()
    {
        $this->mockEntityManager->shouldReceive('flush')->andThrow('\Doctrine\DBAL\Exception\NotNullConstraintViolationException', null, $this->mockDriverException);
        $person = $this->personService->createPerson(self::$firstName, self::$lastName, self::$emailAddress);
    }

    /**
     * Test handling of attempting to persist a person that already exists in persistence.
     *
     * @expectedException \Pelagos\Exception\RecordExistsPersistenceException
     */
    public function testCreatePersonRecordExists()
    {
        $this->mockEntityManager->shouldReceive('flush')->andThrow('\Doctrine\DBAL\Exception\UniqueConstraintViolationException', null, $this->mockDriverException);
        $person = $this->personService->createPerson(self::$firstName, self::$lastName, self::$emailAddress);
    }

    /**
     * Test handling of attempting to persist a person and encountering a persistence error.
     * This tests for handling of persistence errors not handled specifically.
     *
     * @expectedException \Pelagos\Exception\PersistenceException
     */
    public function testCreatePersonPersistenceError()
    {
        $this->mockEntityManager->shouldReceive('flush')->andThrow('\Doctrine\DBAL\DBALException');
        $person = $this->personService->createPerson(self::$firstName, self::$lastName, self::$emailAddress);
    }
}
