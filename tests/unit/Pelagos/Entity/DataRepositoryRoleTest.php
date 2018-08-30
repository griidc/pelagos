<?php

namespace Pelagos\Entity;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Pelagos\Entity\DataRepositoryRole.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\DataRepositoryRole
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
    protected function setUp()
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
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testSetWeightZero()
    {
        $this->dataRepositoryRole->setWeight(0);
    }

    /**
     * Test setting the weight to a negative integer.
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testSetWeightNegative()
    {
        $this->dataRepositoryRole->setWeight(-1);
    }

    /**
     * Test setting the weight with an integer string.
     *
     * @return void
     */
    public function testSetWeightIntegerString()
    {
        $this->dataRepositoryRole->setWeight('1');
        $this->assertEquals(
            self::$testWeight,
            $this->dataRepositoryRole->getWeight()
        );
    }

    /**
     * Test setting the weight with a string containing 0.
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testSetWeightZeroString()
    {
        $this->dataRepositoryRole->setWeight('0');
    }

    /**
     * Test setting the weight with a string containing a negative integer.
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testSetWeightNegativeString()
    {
        $this->dataRepositoryRole->setWeight('-1');
    }

    /**
     * Test setting the weight with a string containing non-digits.
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testSetWeightNonDigitString()
    {
        $this->dataRepositoryRole->setWeight('A');
    }

    /**
     * Test setting the weight with string containing non-digits but starting with a digit.
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testSetWeightPartialDigitStringStart()
    {
        $this->dataRepositoryRole->setWeight('1A');
    }

    /**
     * Test setting the weight with string containing non-digits but ending with a digit.
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testSetWeightPartialDigitStringEnd()
    {
        $this->dataRepositoryRole->setWeight('A1');
    }

    /**
     * Test setting the weight with string containing non-digits but with a digit in the middle.
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testSetWeightPartialDigitStringMiddle()
    {
        $this->dataRepositoryRole->setWeight('A1A');
    }
}
