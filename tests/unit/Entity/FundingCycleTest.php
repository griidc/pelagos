<?php

namespace App\Tests\Entity;

use App\Entity\FundingCycle;
use App\Entity\FundingOrganization;
use App\Entity\Person;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Error;


/**
 * Unit tests for App\Entity\FundingCycle.
 */
class FundingCycleTest extends TestCase
{
    /**
     * Property to hold an instance of FundingCycle for testing.
     *
     * @var FundingCycle $fundingCycle
     */
    protected $fundingCycle;

    /**
     * Static class variable containing a name to use for testing.
     *
     * @var string $testName
     */
    protected static $testName = 'My Funding Cycle';

    /**
     * Static class variable containing a description to use for testing.
     *
     * @var string $testDescription
     */
    protected static $testDescription = 'This is a funding cycle.';

    /**
     * Static class variable containing a URL to use for testing.
     *
     * @var string $testUrl
     */
    protected static $testUrl = 'http://gulfresearchinitiative.org';

    /**
     * Class variable to hold a start date to use for testing.
     *
     * @var string $testStartDate
     */
    protected $testStartDate;

    /**
     * Class variable to hold an end date to use for testing.
     *
     * @var string $testEndDate
     */
    protected $testEndDate;

    /**
     * Property to hold a creator to use for testing.
     *
     * @var Person $testCreator
     */
    protected $testCreator;

    /**
     * Class variable to hold a FundingOrganization to use for testing.
     *
     * @var FundingOrganization $testFundingOrganization
     */
    protected $testFundingOrganization;

    /**
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of FundingCycle.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->testCreator = new Person;
        $this->fundingCycle = new FundingCycle;
        $this->fundingCycle->setName(self::$testName);
        $this->fundingCycle->setDescription(self::$testDescription);
        $this->fundingCycle->setUrl(self::$testUrl);
        $this->fundingCycle->setCreator($this->testCreator);
        $this->testStartDate = new \DateTime('2015-01-01');
        $this->fundingCycle->setStartDate($this->testStartDate);
        $this->testEndDate = new \DateTime('2015-12-31');
        $this->fundingCycle->setEndDate($this->testEndDate);
        $this->testFundingOrganization = \Mockery::mock('\App\Entity\FundingOrganization');
        $this->testFundingOrganization->shouldReceive('jsonSerialize')->andReturn(array('id' => 0));
        $this->fundingCycle->setFundingOrganization($this->testFundingOrganization);
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
            $this->fundingCycle->getName()
        );
    }

    /**
     * Test the getDescription method.
     *
     * This method should return the description that was set in setUp.
     *
     * @return void
     */
    public function testGetDescription()
    {
        $this->assertEquals(
            self::$testDescription,
            $this->fundingCycle->getDescription()
        );
    }

    /**
     * Test the getUrl method.
     *
     * This method should return the URL that was set in setUp.
     *
     * @return void
     */
    public function testGetUrl()
    {
        $this->assertEquals(
            self::$testUrl,
            $this->fundingCycle->getUrl()
        );
    }

    /**
     * Test the getStartDate method.
     *
     * This method should return the start date that was set in setUp.
     *
     * @return void
     */
    public function testGetStartDate()
    {
        $this->assertEquals(
            $this->testStartDate,
            $this->fundingCycle->getStartDate()
        );
    }

    /**
     * Test the getEndDate method.
     *
     * This method should return the end date that was set in setUp.
     *
     * @return void
     */
    public function testGetEndDate()
    {
        $this->assertEquals(
            $this->testEndDate,
            $this->fundingCycle->getEndDate()
        );
    }

    /**
     * Test the getFundingOrganization method.
     *
     * This method should return the FundingOrganization that was set in setUp.
     *
     * @return void
     */
    public function testGetFundingOrganizationInvalid()
    {
        $this->assertSame(
            $this->testFundingOrganization,
            $this->fundingCycle->getFundingOrganization()
        );
    }
}
