<?php

namespace Pelagos\Component;

/**
 * Tests for the Entity service.
 *
 * @runTestsInSeparateProcesses
 *
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

    /**
     * A valid first name to use for testing.
     *
     * @var string $firstName
     */
    protected static $firstName = 'test';

    /**
     * A valid last name to use for testing.
     *
     * @var string $lastName
     */
    protected static $lastName = 'user';

    /**
     * A valid email address to use for testing.
     *
     * @var string $emailAddress
     */
    protected static $emailAddress = 'test.user@testdomian.tld';

    /**
     * An invalid email address to use for testing.
     *
     * @var string $emailAddress
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
        $this->mockEntityRepository = \Mockery::mock('\Doctrine\ORM\EntityRepository');

        $this->mockEntityManager = \Mockery::mock('\Doctrine\ORM\EntityManager');
        $this->mockEntityManager->shouldReceive('persist');
        $this->mockEntityManager->shouldReceive('remove');

        $this->mockEntityManager->shouldReceive('getRepository')->andReturn($this->mockEntityRepository);

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
     *
     * @return void
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
     *
     * @return void
     */
    public function testValidateFailure()
    {
        $this->mockValidator->shouldReceive('validate')->andReturn(array(1));
        $entity = $this->entityService->validate($this->mockEntity, $this->mockValidator);
    }

    /**
     * Test persisting an entity successfully.
     *
     * @return void
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
        $entity = $this->entityService->persist($this->mockEntity);
    }

    /**
     * Test handling of attempting to persist an entity that already exists in persistence.
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
        $entity = $this->entityService->persist($this->mockEntity);
    }

    /**
     * Test handling of attempting to persist an entity and encountering a persistence error.
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
        $entity = $this->entityService->persist($this->mockEntity);
    }

    /**
     * Test getting an entity that exists.
     *
     * Should return the entity for the provided id.
     *
     * @return void
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
     *
     * Should return the entity for the provided id.
     *
     * @return void
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
     *
     * @return void
     */
    public function testGetInvalidID()
    {
        $entity = $this->entityService->get('Entity', 'foo');
    }

    /**
     * Test attempting to get an entity with an invalid id and getting back the id sent upon catching the exception.
     *
     * @return void
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
     *
     * @return void
     */
    public function testGetRecordNotFound()
    {
        $this->mockEntityManager->shouldReceive('find')->andReturnNull();
        $entity = $this->entityService->get('Entity', 0);
    }

    /**
     * Test handling of attempting to get an entity and encountering a persistence error.
     *
     * This tests for handling of persistence errors not handled specifically.
     *
     * @expectedException \Pelagos\Exception\PersistenceException
     *
     * @return void
     */
    public function testGetPersistenceError()
    {
        $this->mockEntityManager->shouldReceive('find')->andThrow('\Doctrine\DBAL\DBALException');
        $entity = $this->entityService->get('Entity', 0);
    }

    /**
     * Test getting all entities.
     *
     * Should return an array of entities for the provided id.
     *
     * @return void
     */
    public function testGetAll()
    {
        $this->mockEntityRepository->shouldReceive('findAll')->andReturn(array($this->mockEntity));
        $entities = $this->entityService->getAll('Entity');
        $this->assertCount(1, $entities);
        $this->assertInstanceOf('\Pelagos\Entity\Entity', $entities[0]);
        $this->assertSame(0, $entities[0]->getId());
    }

    /**
     * Test deleting an entity.
     *
     * @return void
     */
    public function testDeleteSuccess()
    {
        $this->mockEntityManager->shouldReceive('flush');
        $entity = $this->entityService->delete($this->mockEntity);
        $this->assertInstanceOf('\Pelagos\Entity\Entity', $entity);
        $this->assertSame(0, $entity->getId());
    }

    /**
     * Test handling of attempting to delete an entity and encountering a persistence error.
     *
     * @expectedException \Pelagos\Exception\PersistenceException
     *
     * @return void
     */
    public function testDeletePersistenceError()
    {
        $this->mockEntityManager->shouldReceive('flush')->andThrow('\Doctrine\DBAL\DBALException');
        $entity = $this->entityService->delete($this->mockEntity);
    }
}
