<?php

namespace Pelagos\Entity;

/**
 * Unit tests for Pelagos\Entity\ResearchGroup.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\ResearchGroup
 */
class ResearchGroupTest extends \PHPUnit_Framework_TestCase
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
     * @var FundingCycle $testParentFundingCycle
     */
    protected static $testParentFundingCycle;

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
    protected static $testEmailAddress = 'griidc@gomri.org';

    /**
     * Property to hold a funding cycle to use in testing.
     *
     * @var FundingCycle $testParentMockFundingCycle
     */
    protected $testMockParentFundingCycle;

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
        $this->testMockParentFundingCycle = \Mockery::mock('\Pelagos\Entity\FundingCycle');
        $this->testMockParentFundingCycle->shouldReceive('jsonSerialize');
        $this->researchGroup->setParentFundingCycle($this->testMockParentFundingCycle);
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
     * Test the testGetParentFundingCycle() method.
     *
     * This method verify the return of the parent's Funding Cycle
     *
     * @return void
     */
    public function testGetParentFundingCycle()
    {
        $parentFundingCycle = $this->researchGroup->getParentFundingCycle();
        $this->assertInstanceOf('\Pelagos\Entity\FundingCycle', $parentFundingCycle);
    }

    /**
     * Test the testSetParentFundingCycle() method with bad (non-FC) element.
     *
     * This method should result in an exception being thrown.
     *
     * @expectedException \Exception
     *
     * @return void
     */
    public function testSetParentFundingCyclesWithNonFC()
    {
        $this->researchGroup->setParentFundingCycle('string data');
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
     * Test the update method.
     *
     * @return void
     */
    public function testUpdate()
    {
        $this->researchGroup->update(
            array(
                'name' => 'new_name',
                'url' => 'new_url',
                'phoneNumber' => 'new_phoneNumber',
                'deliveryPoint' => 'new_deliveryPoint',
                'city' => 'new_city',
                'administrativeArea' => 'new_administrativeArea',
                'postalCode' => 'new_postalCode',
                'country' => 'new_country',
                'description' => 'new_description',
                'logo' => 'new_logo',
                'emailAddress' => 'new_emailAddress',
            )
        );
        $this->assertEquals(
            'new_name',
            $this->researchGroup->getName()
        );
        $this->assertEquals(
            'new_url',
            $this->researchGroup->getUrl()
        );
        $this->assertEquals(
            'new_phoneNumber',
            $this->researchGroup->getPhoneNumber()
        );
        $this->assertEquals(
            'new_deliveryPoint',
            $this->researchGroup->getDeliveryPoint()
        );
        $this->assertEquals(
            'new_city',
            $this->researchGroup->getCity()
        );
        $this->assertEquals(
            'new_administrativeArea',
            $this->researchGroup->getAdministrativeArea()
        );
        $this->assertEquals(
            'new_postalCode',
            $this->researchGroup->getPostalCode()
        );
        $this->assertEquals(
            'new_country',
            $this->researchGroup->getCountry()
        );
        $this->assertEquals(
            'new_description',
            $this->researchGroup->getDescription()
        );
        $this->assertEquals(
            'new_logo',
            $this->researchGroup->getLogo()
        );
        $this->assertEquals(
            'new_emailAddress',
            $this->researchGroup->getEmailAddress()
        );
    }

    /**
     * Test that ResearchGroup is JsonSerializable and serializes to the expected JSON.
     *
     * @return void
     */
    public function testJsonSerialize()
    {
        $researchGroupData = array(
            'id' => null,
            'creator' => null,
            'creationTimeStamp' => null,
            'modifier' => null,
            'modificationTimeStamp' => null,
            'name' => self::$testName,
            'url' => self::$testUrl,
            'phoneNumber' => self::$testPhoneNumber,
            'deliveryPoint' => self::$testDeliveryPoint,
            'city' => self::$testCity,
            'administrativeArea' => self::$testAdministrativeArea,
            'postalCode' => self::$testPostalCode,
            'country' => self::$testCountry,
            'description' => self::$testDescription,
            'logo' => array('mimeType' => 'text/plain', 'base64' => base64_encode(self::$testLogo)),
            'emailAddress' => self::$testEmailAddress,
        );
        $this->assertEquals(json_encode($researchGroupData), json_encode($this->researchGroup));
    }
}
