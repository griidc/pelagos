<?php

namespace App\Tests\Entity;

use App\Entity\FundingCycle;
use App\Entity\ResearchGroup;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Error;

/**
 * Unit tests for App\Entity\ResearchGroup.
 */
class ResearchGroupTest extends TestCase
{
    /**
     * Property to hold an instance of ResearchGroup for testing.
     *
     * @var ResearchGroup $researchGroup
     */
    protected $researchGroup;

    /**
     * Static class variable containing a name to use for testing.
     *
     * @var string $testName
     */
    protected static $testName = 'Developers Den';

    /**
     * Property to hold a parent funding cycles for testing.
     *
     * @var FundingCycle $testFundingCycle
     */
    protected static $testFundingCycle;

    /**
     * Static class variable containing a URL to use for testing.
     *
     * @var string $testUrl
     */
    protected static $testUrl = 'http://staff.tamucc.edu/mwilliamson';

    /**
     * Static class variable containing a phone number to use for testing.
     *
     * @var string $testPhoneNumber
     */
    protected static $testPhoneNumber = '361-825-2048';

    /**
     * Static class variable containing a delivery point to use for testing.
     *
     * @var string $testDeliveryPoint
     */
    protected static $testDeliveryPoint = '6300 Ocean Dr.';

    /**
     * Static class variable containing a city to use for testing.
     *
     * @var string $testCity
     */
    protected static $testCity = 'Corpus Christi';

    /**
     * Static class variable containing an administrative area to use for testing.
     *
     * @var string $testAdministrativeArea
     */
    protected static $testAdministrativeArea = 'Texas';

    /**
     * Static class variable containing a postal code to use for testing.
     *
     * @var string $testPostalCode
     */
    protected static $testPostalCode = '78412';

    /**
     * Static class variable containing a country to use for testing.
     *
     * @var string $testCountry
     */
    protected static $testCountry = 'USA';

    /**
     * Static class variable containing a description to use for testing.
     *
     * @var string $testDescription
     */
    protected static $testDescription = 'This is an organization that funds stuff. That is all.';

    /**
     * Class variable to hold a logo to use for testing.
     *
     * @var string $testLogo
     */
    protected static $testLogo = '12345';

    /**
     * Static class variable containing an email address to use for testing.
     *
     * @var string $testEmailAddress
     */
    protected static $testEmailAddress = 'help@griidc.org';

    /**
     * Property to hold a funding cycle to use in testing.
     *
     * @var FundingCycle $testParentMockFundingCycle
     */
    protected $testMockFundingCycle;

    /**
     * Property to hold a set of PersonResearchGroup for testing.
     *
     * @var $testPersonResearchGroups
     */
    protected $testPersonResearchGroups;

    /**
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of ResearchGroup.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->researchGroup = new ResearchGroup;
        $this->researchGroup->setName(self::$testName);
        $this->testMockFundingCycle = \Mockery::mock('\App\Entity\FundingCycle');
        $this->testMockFundingCycle->shouldReceive('jsonSerialize');
        $this->researchGroup->setFundingCycle($this->testMockFundingCycle);
        $this->researchGroup->setUrl(self::$testUrl);
        $this->researchGroup->setPhoneNumber(self::$testPhoneNumber);
        $this->researchGroup->setDeliveryPoint(self::$testDeliveryPoint);
        $this->researchGroup->setCity(self::$testCity);
        $this->researchGroup->setAdministrativeArea(self::$testAdministrativeArea);
        $this->researchGroup->setPostalCode(self::$testPostalCode);
        $this->researchGroup->setCountry(self::$testCountry);
        $this->researchGroup->setDescription(self::$testDescription);
        $this->researchGroup->setLogo(self::$testLogo);
        $this->researchGroup->setEmailAddress(self::$testEmailAddress);
        $this->testPersonResearchGroups = array(
            \Mockery::mock(
                '\App\Entity\PersonResearchGroup',
                array(
                    'setResearchGroup' => null
                )
            ),
            \Mockery::mock(
                '\App\Entity\PersonResearchGroup',
                array(
                    'setResearchGroup' => null
                )
            ),
        );
        $this->researchGroup->setPersonResearchGroups($this->testPersonResearchGroups);
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
            $this->researchGroup->getName()
        );
    }

    /**
     * Test the testGetFundingCycle() method.
     *
     * This method verify the return of the parent's Funding Cycle
     *
     * @return void
     */
    public function testGetFundingCycle()
    {
        $fundingCycle = $this->researchGroup->getFundingCycle();
        $this->assertInstanceOf('\App\Entity\FundingCycle', $fundingCycle);
    }

    /**
     * Test the testGetPersonResearchGroups method.
     *
     * This method verify the associated PersonResearchGroups are each an instance of PersonResearchGroup.
     *
     * @return void
     */
    public function testGetPersonResearchGroups()
    {
        $personResearchGroups = $this->researchGroup->getPersonResearchGroups();
        foreach ($personResearchGroups as $personResearchGroup) {
            $this->assertInstanceOf('\App\Entity\PersonResearchGroup', $personResearchGroup);
        }
    }

    /**
     * Test the testSetPersonResearchGroups() method with a non-array/traversable object.
     *
     * This method should result in an exception being thrown.
     *
     * @return void
     */
    public function testSetPersonResearchGroupsWithNonTraversable()
    {
        $this->expectException(\Exception::class);
        $this->researchGroup->setPersonResearchGroups('string data');
    }

    /**
     * Test testSetPersonResearchGroups() method with bad (non-PersonResearchGroup) element in otherwise good array.
     *
     * This method should result in an exception being thrown.
     *
     * @return void
     */
    public function testSetPersonResearchGroupsWithANonPersonResearchGroupInArray()
    {
        $this->expectException(\Exception::class);
        $testArry = $this->testPersonResearchGroups;
        array_push($testArry, 'string data');
        $this->researchGroup->setPersonResearchGroups($testArry);
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
            $this->researchGroup->getUrl()
        );
    }

    /**
     * Test the getPhoneNumber method.
     *
     * This method should return the phone number that was set in setUp.
     *
     * @return void
     */
    public function testGetPhoneNumber()
    {
        $this->assertEquals(
            self::$testPhoneNumber,
            $this->researchGroup->getPhoneNumber()
        );
    }

    /**
     * Test the getDeliveryPoint method.
     *
     * This method should return the delivery point that was set in setUp.
     *
     * @return void
     */
    public function testGetDeliveryPoint()
    {
        $this->assertEquals(
            self::$testDeliveryPoint,
            $this->researchGroup->getDeliveryPoint()
        );
    }

    /**
     * Test the getCity method.
     *
     * This method should return the city that was set in setUp.
     *
     * @return void
     */
    public function testGetCity()
    {
        $this->assertEquals(
            self::$testCity,
            $this->researchGroup->getCity()
        );
    }

    /**
     * Test the getAdministrativeArea method.
     *
     * This method should return the administrative area that was set in setUp.
     *
     * @return void
     */
    public function testGetAdministrativeArea()
    {
        $this->assertEquals(
            self::$testAdministrativeArea,
            $this->researchGroup->getAdministrativeArea()
        );
    }

    /**
     * Test the getPostalCode method.
     *
     * This method should return the postal code that was set in setUp.
     *
     * @return void
     */
    public function testGetPostalCode()
    {
        $this->assertEquals(
            self::$testPostalCode,
            $this->researchGroup->getPostalCode()
        );
    }

    /**
     * Test the getCountry method.
     *
     * This method should return the country that was set in setUp.
     *
     * @return void
     */
    public function testGetCountry()
    {
        $this->assertEquals(
            self::$testCountry,
            $this->researchGroup->getCountry()
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
            $this->researchGroup->getDescription()
        );
    }

    /**
     * Test the getLogo method.
     *
     * This method should return the logo that was set in setUp.
     *
     * @return void
     */
    public function testGetLogo()
    {
        $this->assertEquals(
            self::$testLogo,
            $this->researchGroup->getLogo()
        );
    }

    /**
     * Test the getEmailAddress method.
     *
     * This method should return the email address that was set in setUp.
     *
     * @return void
     */
    public function testGetEmailAddress()
    {
        $this->assertEquals(
            self::$testEmailAddress,
            $this->researchGroup->getEmailAddress()
        );
    }

    /**
     * Test getter and setter for short name attribute.
     *
     * @return void
     */
    public function testGetAndSetShortName(): void
    {
        $shortName = 'CARTHE';
        $this->researchGroup->setShortName($shortName);
        $this->assertEquals($shortName, $this->researchGroup->getShortName());
    }

    /**
     * Test lock/unlock.
     *
     * @return void
     */
    public function testLockAndUnlock(): void
    {
        // Test default for new Research Group object, should be unlocked.
        $this->assertFalse($this->researchGroup->isLocked());

        // Test setting/checking lock and unlock.
        $this->researchGroup->setLocked(true);
        $this->assertTrue($this->researchGroup->isLocked());
        $this->researchGroup->setLocked(false);
        $this->assertFalse($this->researchGroup->isLocked());
    }
}
