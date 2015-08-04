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
    protected $testLogo;

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
        $this->testLogo = file_get_contents(__DIR__ . '/../../../data/gomri-logo.jpg');
        $this->fundingOrganization->setLogo($this->testLogo);
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
            $this->fundingOrganization->getId(),
            null
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
            $this->fundingOrganization->getName(),
            self::$testName
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
            $this->fundingOrganization->getLogo(),
            $this->testLogo
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
            $this->fundingOrganization->getEmailAddress(),
            self::$testEmailAddress
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
            $this->fundingOrganization->getDescription(),
            self::$testDescription
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
            $this->fundingOrganization->getUrl(),
            self::$testUrl
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
            $this->fundingOrganization->getPhoneNumber(),
            self::$testPhoneNumber
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
            $this->fundingOrganization->getDeliveryPoint(),
            self::$testDeliveryPoint
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
            $this->fundingOrganization->getCity(),
            self::$testCity
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
            $this->fundingOrganization->getAdministrativeArea(),
            self::$testAdministrativeArea
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
            $this->fundingOrganization->getPostalCode(),
            self::$testPostalCode
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
            $this->fundingOrganization->getCountry(),
            self::$testCountry
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
            $this->fundingOrganization->getCreator(),
            self::$testCreator
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
            )
        );
        $this->assertEquals(
            $this->fundingOrganization->getName(),
            'new_name'
        );
        $this->assertEquals(
            $this->fundingOrganization->getLogo(),
            'new_logo'
        );
        $this->assertEquals(
            $this->fundingOrganization->getEmailAddress(),
            'new_emailAddress'
        );
        $this->assertEquals(
            $this->fundingOrganization->getDescription(),
            'new_description'
        );
        $this->assertEquals(
            $this->fundingOrganization->getUrl(),
            'new_url'
        );
        $this->assertEquals(
            $this->fundingOrganization->getPhoneNumber(),
            'new_phoneNumber'
        );
        $this->assertEquals(
            $this->fundingOrganization->getDeliveryPoint(),
            'new_deliveryPoint'
        );
        $this->assertEquals(
            $this->fundingOrganization->getCity(),
            'new_city'
        );
        $this->assertEquals(
            $this->fundingOrganization->getAdministrativeArea(),
            'new_administrativeArea'
        );
        $this->assertEquals(
            $this->fundingOrganization->getPostalCode(),
            'new_postalCode'
        );
        $this->assertEquals(
            $this->fundingOrganization->getCountry(),
            'new_country'
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
}
