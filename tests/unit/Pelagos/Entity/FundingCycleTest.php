<?php

namespace Pelagos\Entity;

/**
 * Unit tests for Pelagos\Entity\FundingCycle.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\FundingCycle
 */
class FundingCycleTest extends \PHPUnit_Framework_TestCase
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
     * Static class variable containing username to use as creator.
     *
     * @var string $testCreator
     */
    protected static $testCreator = 'testcreator';

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
    protected function setUp()
    {
        $this->fundingCycle = new FundingCycle;
        $this->fundingCycle->setName(self::$testName);
        $this->fundingCycle->setDescription(self::$testDescription);
        $this->fundingCycle->setUrl(self::$testUrl);
        $this->fundingCycle->setCreator(self::$testCreator);
        $this->testStartDate = new \DateTime('2015-01-01');
        $this->fundingCycle->setStartDate($this->testStartDate);
        $this->testEndDate = new \DateTime('2015-12-31');
        $this->fundingCycle->setEndDate($this->testEndDate);
        $this->testFundingOrganization = \Mockery::mock('\Pelagos\Entity\FundingOrganization');
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
     * Test the setFundingOrganization method with something that is not a FundingOrganization.
     *
     * This method should not accept a parameter that is not a \Pelagos\Entity\FundingOrganization.
     *
     * @expectedException \PHPUnit_Framework_Error
     *
     * @return void
     */
    public function testSetFundingOrganizationInvalid()
    {
        $this->fundingCycle->setFundingOrganization('not a FundingOrganization');
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

    /**
     * Test the update method.
     *
     * @return void
     */
    public function testUpdate()
    {
        $this->fundingCycle->update(
            array(
                'name' => 'new_name',
                'description' => 'new_description',
                'url' => 'new_url',
                'creator' => self::$testCreator,
            )
        );
        $this->assertEquals(
            $this->fundingCycle->getName(),
            'new_name'
        );
        $this->assertEquals(
            $this->fundingCycle->getDescription(),
            'new_description'
        );
        $this->assertEquals(
            $this->fundingCycle->getUrl(),
            'new_url'
        );
        $this->assertEquals(
            $this->fundingCycle->getCreator(),
            self::$testCreator
        );
    }

    /**
     * Test that FundingCycle is JsonSerializable and serializes to the expected JSON.
     *
     * @return void
     */
    public function testJsonSerialize()
    {
        $fundingCycleData = array(
            'id' => null,
            'creator' => self::$testCreator,
            'creationTimeStamp' => null,
            'modifier' => self::$testCreator,
            'modificationTimeStamp' => null,
            'name' => self::$testName,
            'description' => self::$testDescription,
            'url' => self::$testUrl,
            'startDate' => $this->testStartDate->format('Y-m-d'),
            'endDate' => $this->testEndDate->format('Y-m-d'),
            'fundingOrganization' => array('id' => 0),
        );
        $this->assertEquals(json_encode($fundingCycleData), json_encode($this->fundingCycle));
    }
}
