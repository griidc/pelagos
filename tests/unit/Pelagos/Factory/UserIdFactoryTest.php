<?php

namespace Pelagos\Factory;

/**
 * Unit tests for Pelagos\Factory\UserIdFactory.
 *
 * @group Pelagos
 * @group Pelagos\Factory
 * @group Pelagos\Factory\UserIdFactory
 */
class UserIdFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Property to hold a mock instance of Person for testing.
     * @var \Pelagos\Entity\Person
     */
    protected $mockPerson;

    /**
     * Property to hold a mock instance of EntityService for testing.
     * @var \Pelagos\Service\EntityService
     */
    protected $mockEntityService;

    /**
     * Setup for PHPUnit tests.
     *
     * This creates a mock Person and a mock EntityService.
     *
     * @return void
     */
    protected function setUp()
    {
        // Create a mock Person.
        $this->mockPerson = \Mockery::mock('\Pelagos\Entity\Person');
        // Create a mock EntityService.
        $this->mockEntityService = \Mockery::mock('\Pelagos\Service\EntityService');
    }

    /**
     * Make sure we can't instantiate a UserIdFactory externally.
     *
     * @return void
     */
    public function testCantInstantiate()
    {
        $reflection = new \ReflectionClass('\Pelagos\Factory\UserIdFactory');
        $this->assertFalse($reflection->getConstructor()->isPublic());
    }

    /**
     * Test generating a user ID from a name with only ASCII characters with no collisions.
     *
     * @return void
     */
    public function testAsciiNameNoCollisions()
    {
        $this->mockPerson->shouldReceive('getFirstName')->andReturn('Test');
        $this->mockPerson->shouldReceive('getLastName')->andReturn('User');
        $this->mockEntityService->shouldReceive('getDistinctVals')->andReturn(array());
        $this->assertEquals(
            'tuser',
            UserIdFactory::generateUniqueUserId(
                $this->mockPerson,
                $this->mockEntityService
            )
        );
    }

    /**
     * Test generating a user ID from a name with only ASCII characters with one collision.
     *
     * @return void
     */
    public function testAsciiNameOneCollision()
    {
        $this->mockPerson->shouldReceive('getFirstName')->andReturn('Test');
        $this->mockPerson->shouldReceive('getLastName')->andReturn('User');
        $this->mockEntityService->shouldReceive('getDistinctVals')->andReturn(array('tuser'));
        $this->assertEquals(
            'tuser2',
            UserIdFactory::generateUniqueUserId(
                $this->mockPerson,
                $this->mockEntityService
            )
        );
    }

    /**
     * Test generating a user ID from a name with only ASCII characters with two collisions.
     *
     * @return void
     */
    public function testAsciiNameTwoCollisions()
    {
        $this->mockPerson->shouldReceive('getFirstName')->andReturn('Test');
        $this->mockPerson->shouldReceive('getLastName')->andReturn('User');
        $this->mockEntityService->shouldReceive('getDistinctVals')->andReturn(array('tuser','tuser2'));
        $this->assertEquals(
            'tuser3',
            UserIdFactory::generateUniqueUserId(
                $this->mockPerson,
                $this->mockEntityService
            )
        );
    }

    /**
     * Test generating a user ID from a name that contains non-ASCII characters.
     *
     * @return void
     */
    public function testNonAsciiName()
    {
        $this->mockPerson->shouldReceive('getFirstName')->andReturn('люблю');
        $this->mockPerson->shouldReceive('getLastName')->andReturn('Übérmensch');
        $this->mockEntityService->shouldReceive('getDistinctVals')->andReturn(array());
        $this->assertEquals(
            'lubermensch',
            UserIdFactory::generateUniqueUserId(
                $this->mockPerson,
                $this->mockEntityService
            )
        );
    }
}
