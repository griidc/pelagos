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
     * The user ID should be the lowercased first intital prepended to the lowercased last name.
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
     * The user ID should include a single digit uniquifier.
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
     * The user ID should include a single digit uniquifier.
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
     * Test generating a user ID from a name with only ASCII characters with nine collisions.
     *
     * The user ID should include a two-digit uniquifier.
     *
     * @return void
     */
    public function testAsciiNameNineCollisions()
    {
        $this->mockPerson->shouldReceive('getFirstName')->andReturn('Test');
        $this->mockPerson->shouldReceive('getLastName')->andReturn('User');
        $this->mockEntityService->shouldReceive('getDistinctVals')->andReturn(
            array(
                'tuser',
                'tuser2',
                'tuser3',
                'tuser4',
                'tuser5',
                'tuser6',
                'tuser7',
                'tuser8',
                'tuser9',
            )
        );
        $this->assertEquals(
            'tuser10',
            UserIdFactory::generateUniqueUserId(
                $this->mockPerson,
                $this->mockEntityService
            )
        );
    }

    /**
     * Test generating a user ID from a name that contains non-ASCII characters.
     *
     * The user ID should the ascii transliterated lowercased first intital
     * prepended to the ascii transliterated lowercased last name.
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

    /**
     * Test generating a user ID from a name that contains spaces.
     *
     * The user ID should have spaces removed.
     *
     * @return void
     */
    public function testNameWithSpaces()
    {
        $this->mockPerson->shouldReceive('getFirstName')->andReturn('Robin');
        $this->mockPerson->shouldReceive('getLastName')->andReturn('van den Hood');
        $this->mockEntityService->shouldReceive('getDistinctVals')->andReturn(array());
        $this->assertEquals(
            'rvandenhood',
            UserIdFactory::generateUniqueUserId(
                $this->mockPerson,
                $this->mockEntityService
            )
        );
    }

    /**
     * Test generating a user ID from a name that contains spaces.
     *
     * The user ID should have spaces removed.
     *
     * @return void
     */
    public function testShortName()
    {
        $this->mockPerson->shouldReceive('getFirstName')->andReturn('a');
        $this->mockPerson->shouldReceive('getLastName')->andReturn('b');
        $this->mockEntityService->shouldReceive('getDistinctVals')->andReturn(array());
        $this->assertEquals(
            'ab',
            UserIdFactory::generateUniqueUserId(
                $this->mockPerson,
                $this->mockEntityService
            )
        );
    }

    /**
     * Test generating a user ID from a first name that contains no valid characters.
     *
     * The user ID should consist of only the last name.
     *
     * @return void
     */
    public function testUnusableFirstName()
    {
        $this->mockPerson->shouldReceive('getFirstName')->andReturn('$');
        $this->mockPerson->shouldReceive('getLastName')->andReturn('User');
        $this->mockEntityService->shouldReceive('getDistinctVals')->andReturn(array());
        $this->assertEquals(
            'user',
            UserIdFactory::generateUniqueUserId(
                $this->mockPerson,
                $this->mockEntityService
            )
        );
    }

    /**
     * Test generating a user ID from a last name that contains no valid characters.
     *
     * @expectedException \Exception
     *
     * @return void
     */
    public function testUnusableLastName()
    {
        $this->mockPerson->shouldReceive('getFirstName')->andReturn('Test');
        $this->mockPerson->shouldReceive('getLastName')->andReturn('#');
        $this->mockEntityService->shouldReceive('getDistinctVals')->andReturn(array());
        UserIdFactory::generateUniqueUserId(
            $this->mockPerson,
            $this->mockEntityService
        );
    }

    /**
     * Test generating a user ID from a very long name with no collisions.
     *
     * The user ID should be truncated to 32 characters.
     *
     * @return void
     */
    public function testLongNameNoCollisions()
    {
        $this->mockPerson->shouldReceive('getFirstName')->andReturn('Bob');
        $this->mockPerson->shouldReceive('getLastName')->andReturn(
            'Llanfairpwllgwyngyllgogerychwyrndrobwllllantysiliogogogoch'
        );
        $this->mockEntityService->shouldReceive('getDistinctVals')->andReturn(array());
        $userId
            = UserIdFactory::generateUniqueUserId(
                $this->mockPerson,
                $this->mockEntityService
            );
        $this->assertLessThanOrEqual(32, strlen($userId));
        $this->assertEquals('bllanfairpwllgwyngyllgogerychwyr', $userId);
    }

    /**
     * Test generating a user ID from a very long name with one collision.
     *
     * The user ID should be truncated to 32 characters but include the uniquifier.
     *
     * @return void
     */
    public function testLongNameOneCollision()
    {
        $this->mockPerson->shouldReceive('getFirstName')->andReturn('Bob');
        $this->mockPerson->shouldReceive('getLastName')->andReturn(
            'Llanfairpwllgwyngyllgogerychwyrndrobwllllantysiliogogogoch'
        );
        $this->mockEntityService->shouldReceive('getDistinctVals')->andReturn(
            array('bllanfairpwllgwyngyllgogerychwyr')
        );
        $userId
            = UserIdFactory::generateUniqueUserId(
                $this->mockPerson,
                $this->mockEntityService
            );
        $this->assertLessThanOrEqual(32, strlen($userId));
        $this->assertEquals('bllanfairpwllgwyngyllgogerychwy2', $userId);
    }

    /**
     * Test generating a user ID from a very long name with two collisions.
     *
     * The user ID should be truncated to 32 characters but include the uniquifier.
     *
     * @return void
     */
    public function testLongNameTwoCollisions()
    {
        $this->mockPerson->shouldReceive('getFirstName')->andReturn('Bob');
        $this->mockPerson->shouldReceive('getLastName')->andReturn(
            'Llanfairpwllgwyngyllgogerychwyrndrobwllllantysiliogogogoch'
        );
        $this->mockEntityService->shouldReceive('getDistinctVals')->andReturn(
            array(
                'bllanfairpwllgwyngyllgogerychwyr',
                'bllanfairpwllgwyngyllgogerychwy2',
            )
        );
        $userId
            = UserIdFactory::generateUniqueUserId(
                $this->mockPerson,
                $this->mockEntityService
            );
        $this->assertLessThanOrEqual(32, strlen($userId));
        $this->assertEquals('bllanfairpwllgwyngyllgogerychwy3', $userId);
    }

    /**
     * Test generating a user ID from a very long name with nine collisions.
     *
     * The user ID should be truncated to 32 characters but include the two-digit uniquifier.
     *
     * @return void
     */
    public function testLongNameNineCollisions()
    {
        $this->mockPerson->shouldReceive('getFirstName')->andReturn('Bob');
        $this->mockPerson->shouldReceive('getLastName')->andReturn(
            'Llanfairpwllgwyngyllgogerychwyrndrobwllllantysiliogogogoch'
        );
        $this->mockEntityService->shouldReceive('getDistinctVals')->andReturn(
            array(
                'bllanfairpwllgwyngyllgogerychwyr',
                'bllanfairpwllgwyngyllgogerychwy2',
                'bllanfairpwllgwyngyllgogerychwy3',
                'bllanfairpwllgwyngyllgogerychwy4',
                'bllanfairpwllgwyngyllgogerychwy5',
                'bllanfairpwllgwyngyllgogerychwy6',
                'bllanfairpwllgwyngyllgogerychwy7',
                'bllanfairpwllgwyngyllgogerychwy8',
                'bllanfairpwllgwyngyllgogerychwy9',
            )
        );
        $userId
            = UserIdFactory::generateUniqueUserId(
                $this->mockPerson,
                $this->mockEntityService
            );
        $this->assertLessThanOrEqual(32, strlen($userId));
        $this->assertEquals('bllanfairpwllgwyngyllgogerychw10', $userId);
    }
}
