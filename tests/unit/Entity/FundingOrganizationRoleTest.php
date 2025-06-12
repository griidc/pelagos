<?php

namespace App\Tests\Entity;

use App\Entity\FundingOrganizationRole;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Entity\FundingOrganizationRole.
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
    protected function setUp(): void
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
     * @return void
     */
    public function testSetWeightZero()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->fundingOrganizationRole->setWeight(0);
    }

    /**
     * Test setting the weight to a negative integer.
     *
     * @return void
     */
    public function testSetWeightNegative()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->fundingOrganizationRole->setWeight(-1);
    }
}
