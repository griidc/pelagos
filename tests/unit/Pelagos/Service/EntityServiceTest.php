<?php

namespace Pelagos\Component;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class EntityServiceTest extends \PHPUnit_Framework_TestCase
{
    /** 
     * Property to hold an instance of EntityService.
     *
     * @var \Pelagos\Service\EntityService $entityService 
     */
    protected $entityService;

    /** 
     * Property to hold a mock entity for testing.
     *
     * @var \Pelagos\Entity\Entity $mockEntity
     */
    protected $mockEntity;

    /**
     * Propety to hold a mock EntityManager. 
     *
     * @var \Doctrine\ORM\EntityManager $mockEntityManager
     */
    protected $mockEntityManager;

    /**
     * Propety to hold a mock DriverException.
     *
     * @var \Doctrine\DBAL\Driver\DriverException $mockDriverException
     */
    protected $mockDriverException;

    /**
     * Propety to hold a mock validator.
     *
     * @var mixed $mockValidator
     */
    protected $mockValidator;



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
        $this->mockEntityManager = \Mockery::mock('\Doctrine\ORM\EntityManager');
        $this->mockEntityManager->shouldReceive('persist');
        $this->entityService = new \Pelagos\Service\EntityService($this->mockEntityManager);

        $this->mockEntity = \Mockery::mock('overload:\Pelagos\Entity\Entity');
        $this->mockEntity->shouldReceive('getId')->andReturn(0);



        $mockPersistence = \Mockery::mock('alias:\Pelagos\Persistance');
        $mockPersistence->shouldReceive('createEntityManager')->andReturn($this->mockEntityManager);

        $this->mockDriverException = \Mockery::mock('\Doctrine\DBAL\Driver\DriverException');

        $this->mockValidator = \Mockery::mock('\Symfony\Component\Validator\Validator');
    }

    /**
     * Test validating an entity with success.
     */
    public function testValidateSuccess()
    {
        $this->mockValidator->shouldReceive('validate')->andReturn(array());
        $entity = $this->entityService->validate($this->mockEntity, $this->mockValidator);
        $this->assertInstanceOf('\Pelagos\Entity\Entity', $entity);
    }

    /**
     * Test validating an entity with validation failure.
     *
     * @expectedException \Pelagos\Exception\ValidationException
     */
    public function testValidateFailure()
    {
        $this->mockValidator->shouldReceive('validate')->andReturn(array(1));
        $entity = $this->entityService->validate($this->mockEntity, $this->mockValidator);
    }

    /**
     * Test persisting an entity successfully.
     */
    public function testPersistSuccess()
    {
        $this->mockEntityManager->shouldReceive('flush');
        $entity = $this->entityService->persist($this->mockEntity);
        $this->assertInstanceOf('\Pelagos\Entity\Entity', $entity);
        $this->assertSame(0, $entity->getId());
    }

    /**
     * Test handling of attempting to persist an entity with a missing required field.
     *
     * @expectedException \Pelagos\Exception\MissingRequiredFieldPersistenceException
     */
    public function testPersistMissingRequiredField()
    {
        $this->mockEntityManager->shouldReceive('flush')->andThrow(
            '\Doctrine\DBAL\Exception\NotNullConstraintViolationException',
            null,
            $this->mockDriverException
        );
        $entity = $this->entityService->persist($this->mockEntity);
    }

    /**
     * Test handling of attempting to persist an entity that already exists in persistence.
     *
     * @expectedException \Pelagos\Exception\RecordExistsPersistenceException
     */
    public function testPersistRecordExists()
    {
        $this->mockEntityManager->shouldReceive('flush')->andThrow(
            '\Doctrine\DBAL\Exception\UniqueConstraintViolationException',
            null,
            $this->mockDriverException
        );
        $entity = $this->entityService->persist($this->mockEntity);
    }

    /**
     * Test handling of attempting to persist an entity and encountering a persistence error.
     * This tests for handling of persistence errors not handled specifically.
     *
     * @expectedException \Pelagos\Exception\PersistenceException
     */
    public function testPersistPersistenceError()
    {
        $this->mockEntityManager->shouldReceive('flush')->andThrow('\Doctrine\DBAL\DBALException');
        $entity = $this->entityService->persist($this->mockEntity);
    }

    /**
     * Test getting an entity that exists.
     * Should return the entity for the provided id.
     */
    public function testGet()
    {
        $this->mockEntityManager->shouldReceive('find')->andReturn($this->mockEntity);
        $entity = $this->entityService->get('Entity', 0);
        $this->assertInstanceOf('\Pelagos\Entity\Entity', $entity);
        $this->assertSame(0, $entity->getId());
    }

    /**
     * Test getting an entity that exists by passing a string that contains an integer.
     * Should return the entity for the provided id.
     */
    public function testGetIntegerString()
    {
        $this->mockEntityManager->shouldReceive('find')->andReturn($this->mockEntity);
        $entity = $this->entityService->get('Entity', '0');
        $this->assertInstanceOf('\Pelagos\Entity\Entity', $entity);
        $this->assertSame(0, $entity->getId());
    }

    /**
     * Test handling of attempting to get an entity with an invalid id.
     *
     * @expectedException \Pelagos\Exception\ArgumentException
     */
    public function testGetInvalidID()
    {
        $entity = $this->entityService->get('Entity', 'foo');
    }

    /**
     * Test handling of attempting to get an entity with an invalid id
     * and getting back the id sent upon catching the exception.
     */
    public function testGetInvalidIDGetID()
    {
        try {
            $entity = $this->entityService->get('Entity', 'foo');
        } catch (\Pelagos\Exception\ArgumentException $e) {
            $this->assertEquals('id', $e->getArgumentName());
            $this->assertEquals('foo', $e->getArgumentValue());
        }
    }

    /**
     * Test handling of attempting to get an entity that does not exist in persistence.
     *
     * @expectedException \Pelagos\Exception\RecordNotFoundPersistenceException
     */
    public function testGetRecordNotFound()
    {
        $this->mockEntityManager->shouldReceive('find')->andReturnNull();
        $entity = $this->entityService->get('Entity', 0);
    }

    /**
     * Test handling of attempting to get an entity and encountering a persistence error.
     * This tests for handling of persistence errors not handled specifically.
     *
     * @expectedException \Pelagos\Exception\PersistenceException
     */
    public function testGetPersistenceError()
    {
        $this->mockEntityManager->shouldReceive('find')->andThrow('\Doctrine\DBAL\DBALException');
        $entity = $this->entityService->get('Entity', 0);
    }
}
