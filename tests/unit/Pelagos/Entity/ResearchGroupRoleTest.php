<?php

namespace Pelagos\Entity;

/**
 * Unit tests for Pelagos\Entity\ResearchGroupRole.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\ResearchGroupRole
 */
class ResearchGroupRoleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Property to hold an instance of ResearchGroupRole for testing.
     *
     * @var ResearchGroupRole $researchGroupRole
     */
    protected $researchGroupRole;

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
     * This instantiates an instance of ResearchGroupRole.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->researchGroupRole = new ResearchGroupRole;
        $this->researchGroupRole->setName(self::$testName);
        $this->researchGroupRole->setWeight(self::$testWeight);
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
            $this->researchGroupRole->getName()
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
            $this->researchGroupRole->getWeight()
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
        $this->researchGroupRole->setWeight(0);
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
        $this->researchGroupRole->setWeight(-1);
    }

    /**
     * Test setting the weight with an integer string.
     *
     * @return void
     */
    public function testSetWeightIntegerString()
    {
        $this->researchGroupRole->setWeight('1');
        $this->assertEquals(
            self::$testWeight,
            $this->researchGroupRole->getWeight()
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
        $this->researchGroupRole->setWeight('0');
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
        $this->researchGroupRole->setWeight('-1');
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
        $this->researchGroupRole->setWeight('A');
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
        $this->researchGroupRole->setWeight('1A');
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
        $this->researchGroupRole->setWeight('A1');
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
        $this->researchGroupRole->setWeight('A1A');
    }
}
