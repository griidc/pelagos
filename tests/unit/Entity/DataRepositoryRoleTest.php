<?php

namespace App\Tests\Entity;

use App\Entity\DataRepositoryRole;
use http\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Entity\DataRepositoryRole.
 */
class DataRepositoryRoleTest extends TestCase
{
    /**
     * Property to hold an instance of DataRepositoryRole for testing.
     *
     * @var DataRepositoryRole $dataRepositoryRole
     */
    protected $dataRepositoryRole;

    /**
     * Static class variable containing name to use for testing.
     *
     * @var string $testName
     */
    protected static $testName = 'Dictator';

    /**
     * Static class variable containing a weight to use for testing.
     *
     * @var string $testWeight
     */
    protected static $testWeight = 1;

    /**
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of DataRepositoryRole.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->dataRepositoryRole = new DataRepositoryRole;
        $this->dataRepositoryRole->setName(self::$testName);
        $this->dataRepositoryRole->setWeight(self::$testWeight);
    }

    /**
     * Test the getName method.
     *
     * This method should return the name that was set in setUp.
     *
     * @return void
     */
    public function testGetName()
    {
        $this->assertEquals(
            self::$testName,
            $this->dataRepositoryRole->getName()
        );
    }

    /**
     * Test the getWeight method.
     *
     * This method should return the weight that was set in setUp.
     *
     * @return void
     */
    public function testGetWeight()
    {
        $this->assertEquals(
            self::$testWeight,
            $this->dataRepositoryRole->getWeight()
        );
    }

    /**
     * Test setting the weight to zero.
     *
     * @return void
     */
    public function testSetWeightZero()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->dataRepositoryRole->setWeight(0);
    }

    /**
     * Test setting the weight to a negative integer.
     *
     * @return void
     */
    public function testSetWeightNegative()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->dataRepositoryRole->setWeight(-1);
    }

    /**
     * Test setting the weight with an integer.
     *
     * @return void
     */
    public function testSetWeightInteger()
    {
        $this->dataRepositoryRole->setWeight(1);
        $this->assertEquals(
            self::$testWeight,
            $this->dataRepositoryRole->getWeight()
        );
    }
}
