<?php

namespace Pelagos\Entity;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Pelagos\Entity\FundingOrganizationRole.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\FundingOrganizationRole
 */
class FundingOrganizationRoleTest extends TestCase
{
    /**
     * Property to hold an instance of FundingOrganizationRole for testing.
     *
     * @var FundingOrganizationRole $fundingOrganizationRole
     */
    protected $fundingOrganizationRole;

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
     * This instantiates an instance of FundingOrganizationRole.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->fundingOrganizationRole = new FundingOrganizationRole;
        $this->fundingOrganizationRole->setName(self::$testName);
        $this->fundingOrganizationRole->setWeight(self::$testWeight);
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
            $this->fundingOrganizationRole->getName()
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
            $this->fundingOrganizationRole->getWeight()
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
        $this->fundingOrganizationRole->setWeight(0);
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
        $this->fundingOrganizationRole->setWeight(-1);
    }

    /**
     * Test setting the weight with an integer string.
     *
     * @return void
     */
    public function testSetWeightIntegerString()
    {
        $this->fundingOrganizationRole->setWeight('1');
        $this->assertEquals(
            self::$testWeight,
            $this->fundingOrganizationRole->getWeight()
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
        $this->fundingOrganizationRole->setWeight('0');
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
        $this->fundingOrganizationRole->setWeight('-1');
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
        $this->fundingOrganizationRole->setWeight('A');
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
        $this->fundingOrganizationRole->setWeight('1A');
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
        $this->fundingOrganizationRole->setWeight('A1');
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
        $this->fundingOrganizationRole->setWeight('A1A');
    }
}
