<?php

namespace Pelagos\Entity;

/**
 * Unit tests for Pelagos\Entity\FundingOrganization.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\FundingOrganization
 */
class FundingOrganizationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Property to hold an instance of FundingOrganization for testing.
     *
     * @var FundingOrganization $fundingOrganization
     */
    protected $fundingOrganization;

    /**
     * Property to hold set of funding cycles for testing.
     *
     * @var testFundingCycles $testFundingCycles
     */
    protected $testFundingCycles;

    /**
     * Static class variable containing a name to use for testing.
     *
     * @var string $testName
     */
    protected static $testName = 'My Funding Organization';

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
     * Static class variable containing a description to use for testing.
     *
     * @var string $testDescription
     */
    protected static $testDescription = 'This is an organization that funds stuff. That is all.';

    /**
     * Static class variable containing a URL to use for testing.
     *
     * @var string $testUrl
     */
    protected static $testUrl = 'http://gulfresearchinitiative.org';

    /**
     * Static class variable containing a phone number to use for testing.
     *
     * @var string $testPhoneNumber
     */
    protected static $testPhoneNumber = '555-555-5555';

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
     * Static class variable containing username to use as creator.
     *
     * @var string $testCreator
     */
    protected static $testCreator = 'testcreator';

    /**
     * Property to hold a time stamp to use in testing.
     *
     * @var \DateTime $timeStamp
     */
    protected $timeStamp;

    /**
     * Property to hold an ISO 8601 representation of a time stamp to use in testing.
     *
     * @var string $timeStampISO
     */
    protected $timeStampISO;

    /**
     * Property to hold a localized time stamp to use in testing.
     *
     * @var \DateTime $timeStampLocalized
     */
    protected $timeStampLocalized;

    /**
     * Property to hold an ISO 8601 representation of a localized time stamp to use in testing.
     *
     * @var string $timeStampLocalizedISO
     */
    protected $timeStampLocalizedISO;

    /**
     * Property to hold a funding cycle to use in testing.
     *
     * @var FundingCycle $testMockFundingCycle
     */
    protected $testMockFundingCycle;

    /**
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of FundingOrganization.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->fundingOrganization = new FundingOrganization;
        $this->fundingOrganization->setName(self::$testName);
        $this->fundingOrganization->setLogo(self::$testLogo);
        $this->fundingOrganization->setEmailAddress(self::$testEmailAddress);
        $this->fundingOrganization->setDescription(self::$testDescription);
        $this->fundingOrganization->setUrl(self::$testUrl);
        $this->fundingOrganization->setPhoneNumber(self::$testPhoneNumber);
        $this->fundingOrganization->setDeliveryPoint(self::$testDeliveryPoint);
        $this->fundingOrganization->setCity(self::$testCity);
        $this->fundingOrganization->setAdministrativeArea(self::$testAdministrativeArea);
        $this->fundingOrganization->setPostalCode(self::$testPostalCode);
        $this->fundingOrganization->setCountry(self::$testCountry);
        $this->fundingOrganization->setCreator(self::$testCreator);

        $this->testMockFundingCycle = \Mockery::mock('\Pelagos\Entity\FundingCycle');
        $this->testMockFundingCycle->shouldReceive('setFundingOrganization');
        $this->fundingOrganization->setFundingCycles(array($this->testMockFundingCycle));

        $this->timeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->timeStampISO = $this->timeStamp->format(\DateTime::ISO8601);
        $this->timeStampLocalized = clone $this->timeStamp;
        $this->timeStampLocalized->setTimeZone(new \DateTimeZone(date_default_timezone_get()));
        $this->timeStampLocalizedISO = $this->timeStampLocalized->format(\DateTime::ISO8601);

    }

    /**
     * Test the getId method.
     *
     * This method should always return null because it can not be set (even by the constructor).
     * The id property can only be set when a FundingOrganization is instantiated from persistence by Doctrine.
     *
     * @return void
     */
    public function testGetID()
    {
        $this->assertEquals(
            null,
            $this->fundingOrganization->getId()
        );
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
            $this->fundingOrganization->getName()
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
            $this->fundingOrganization->getLogo()
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
            $this->fundingOrganization->getEmailAddress()
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
            $this->fundingOrganization->getDescription()
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
            $this->fundingOrganization->getUrl()
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
            $this->fundingOrganization->getPhoneNumber()
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
            $this->fundingOrganization->getDeliveryPoint()
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
            $this->fundingOrganization->getCity()
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
            $this->fundingOrganization->getAdministrativeArea()
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
            $this->fundingOrganization->getPostalCode()
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
            $this->fundingOrganization->getCountry()
        );
    }

    /**
     * Test the getCreator method.
     *
     * This method should return the creator that was set in setUp.
     *
     * @return void
     */
    public function testGetCreator()
    {
        $this->assertEquals(
            self::$testCreator,
            $this->fundingOrganization->getCreator()
        );
    }
    /**
     * Test the setCreationTimeStamp method.
     *
     * This method should accept a \DateTime object in UTC.
     * We should be able to get back the same timestamp in UTC
     * if we call getCreationTimeStamp(false) (non-localized).
     *
     * @return void
     */
    public function testSetCreationTimeStamp()
    {
        $timeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
        $timeStampISO = $timeStamp->format(\DateTime::ISO8601);
        $this->fundingOrganization->setCreationTimeStamp($timeStamp);
        $creationTimeStamp = $this->fundingOrganization->getCreationTimeStamp(false);
        $this->assertInstanceOf('\DateTime', $creationTimeStamp);
        $this->assertEquals($timeStampISO, $creationTimeStamp->format(\DateTime::ISO8601));
    }

    /**
     * Test the setCreationTimeStamp method with a non-UTC timestamp.
     *
     * @return void
     *
     * @expectedException \Exception
     */
    public function testSetCreationTimeStampFailForNonUTC()
    {
        $this->fundingOrganization->setCreationTimeStamp(
            new \DateTime('now', new \DateTimeZone('America/Chicago'))
        );
    }

    /**
     * Test the getCreationTimeStamp method.
     *
     * This method should return a \DateTime object in UTC.
     *
     * @return void
     */
    public function testGetCreationTimeStamp()
    {
        $this->fundingOrganization->setCreationTimeStamp($this->timeStamp);
        $creationTimeStamp = $this->fundingOrganization->getCreationTimeStamp();
        $this->assertInstanceOf('\DateTime', $creationTimeStamp);
        $this->assertEquals(
            'UTC',
            $creationTimeStamp->getTimezone()->getName()
        );
        $this->assertEquals($this->timeStamp, $creationTimeStamp);
    }

    /**
     * Test the getCreationTimeStamp method (localized).
     *
     * This method should return a \DateTime object localized to the current timezone.
     *
     * @return void
     */
    public function testGetCreationTimeStampLocalized()
    {
        $this->fundingOrganization->setCreationTimeStamp($this->timeStamp);
        $creationTimeStamp = $this->fundingOrganization->getCreationTimeStamp(true);
        $this->assertInstanceOf('\DateTime', $creationTimeStamp);
        $this->assertEquals(
            date_default_timezone_get(),
            $creationTimeStamp->getTimezone()->getName()
        );
        $this->assertEquals($this->timeStamp, $creationTimeStamp);
    }

    /**
     * Test the getCreationTimeStampAsISO method.
     *
     * This method should return a string containing the ISO 8601 representation
     * of the creation time stamp localized to the current timezone.
     *
     * @return void
     */
    public function testGetCreationTimeStampAsISO()
    {
        $this->fundingOrganization->setCreationTimeStamp($this->timeStamp);
        $this->assertEquals(
            $this->timeStampISO,
            $this->fundingOrganization->getCreationTimeStampAsISO()
        );
    }

    /**
     * Test the getCreationTimeStampAsISO method.
     *
     * This method should return a string containing the ISO 8601 representation
     * of the creation time stamp localized to the current timezone.
     *
     * @return void
     */
    public function testGetCreationTimeStampAsISOLocalized()
    {
        $this->fundingOrganization->setCreationTimeStamp($this->timeStamp);
        $this->assertEquals(
            $this->timeStampLocalizedISO,
            $this->fundingOrganization->getCreationTimeStampAsISO(true)
        );
    }

    /**
     * Test the getCreationTimeStampAsISO method when creationTimeStamp is null.
     *
     * This method should return null in this case.
     *
     * @return void
     */
    public function testGetCreationTimeStampAsISONull()
    {
        $this->assertNull($this->fundingOrganization->getCreationTimeStampAsISO());
    }

    /**
     * Test the testGetFundingCycles() method.
     *
     * This method verify the return a set of Funding Cycles.
     *
     * @return void
     */
    public function testGetFundingCycles()
    {
        $fundingCycles = $this->fundingOrganization->getFundingCycles();
        foreach ($fundingCycles as $fc) {
            $this->assertInstanceOf('\Pelagos\Entity\FundingCycle', $fc);
        }
    }

    /**
     * Test the testSetFundingCycles() method with bad (non-FC) element.
     *
     * This method should result in an exception being thrown.
     *
     * @expectedException \Exception
     *
     * @return void
     */
    public function testSetFundingCyclesWithNonFC()
    {
        $this->fundingOrganization->setFundingCycles('string data');
    }

    /**
     * Test the testSetFundingCycles() method with a bad (non-FC) element included in set.
     *
     * This method should result in an exception being thrown.
     *
     * @expectedException \Exception
     *
     * @return void
     */
    public function testSetFundingCyclesWithNonFCElement()
    {
        $this->fundingOrganization->setFundingCycles(array($this->testMockFundingCycle, 'string data'));
    }

    /**
     * Test the update method.
     *
     * @return void
     */
    public function testUpdate()
    {
        $this->fundingOrganization->update(
            array(
                'name' => 'new_name',
                'logo' => 'new_logo',
                'emailAddress' => 'new_emailAddress',
                'description' => 'new_description',
                'url' => 'new_url',
                'phoneNumber' => 'new_phoneNumber',
                'deliveryPoint' => 'new_deliveryPoint',
                'city' => 'new_city',
                'administrativeArea' => 'new_administrativeArea',
                'postalCode' => 'new_postalCode',
                'country' => 'new_country',
                'creator' => 'new_creator',
            )
        );
        $this->assertEquals(
            'new_name',
            $this->fundingOrganization->getName()
        );
        $this->assertEquals(
            'new_logo',
            $this->fundingOrganization->getLogo()
        );
        $this->assertEquals(
            'new_emailAddress',
            $this->fundingOrganization->getEmailAddress()
        );
        $this->assertEquals(
            'new_description',
            $this->fundingOrganization->getDescription()
        );
        $this->assertEquals(
            'new_url',
            $this->fundingOrganization->getUrl()
        );
        $this->assertEquals(
            'new_phoneNumber',
            $this->fundingOrganization->getPhoneNumber()
        );
        $this->assertEquals(
            'new_deliveryPoint',
            $this->fundingOrganization->getDeliveryPoint()
        );
        $this->assertEquals(
            'new_city',
            $this->fundingOrganization->getCity()
        );
        $this->assertEquals(
            'new_administrativeArea',
            $this->fundingOrganization->getAdministrativeArea()
        );
        $this->assertEquals(
            'new_postalCode',
            $this->fundingOrganization->getPostalCode()
        );
        $this->assertEquals(
            'new_country',
            $this->fundingOrganization->getCountry()
        );
        $this->assertEquals(
            'new_creator',
            $this->fundingOrganization->getCreator()
        );
    }

    /**
     * Test that FundingOrganization is JsonSerializable and serializes to the expected JSON.
     *
     * @return void
     */
    public function testJsonSerialize()
    {
        $fundingOrganizationData = array(
            'id' => null,
            'creationTimeStamp' => null,
            'creator' => self::$testCreator,
            'name' => self::$testName,
            'emailAddress' => self::$testEmailAddress,
            'description' => self::$testDescription,
            'url' => self::$testUrl,
            'phoneNumber' => self::$testPhoneNumber,
            'deliveryPoint' => self::$testDeliveryPoint,
            'city' => self::$testCity,
            'administrativeArea' => self::$testAdministrativeArea,
            'postalCode' => self::$testPostalCode,
            'country' => self::$testCountry,
            'modificationTimeStamp' => null,
            'modifier' => self::$testCreator,
        );
        $this->assertEquals(json_encode($fundingOrganizationData), json_encode($this->fundingOrganization));
    }

    /**
     * Test that FundingOrganization can be returned as an array via asArray(arry) method.
     *
     * @return void
     */
    public function testAsArray()
    {
        $fundingOrganizationProperties = array(
            'id',
            'creationTimeStamp',
            'creator',
            'name',
            'emailAddress',
            'description',
            'url',
            'phoneNumber',
            'deliveryPoint',
            'city',
            'administrativeArea',
            'postalCode',
            'country',
            'modificationTimeStamp',
            'modifier',
            'logo',
        );
        $fundingOrganizationData = array(
            null,
            null,
            self::$testCreator,
            self::$testName,
            self::$testEmailAddress,
            self::$testDescription,
            self::$testUrl,
            self::$testPhoneNumber,
            self::$testDeliveryPoint,
            self::$testCity,
            self::$testAdministrativeArea,
            self::$testPostalCode,
            self::$testCountry,
            null,
            self::$testCreator,
            self::$testLogo,
        );
        $this->assertEquals(
            $fundingOrganizationData,
            $this->fundingOrganization->asArray(
                $fundingOrganizationProperties
            )
        );
    }
}
