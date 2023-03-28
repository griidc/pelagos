<?php

namespace App\Tests\Entity;

use App\Entity\Funder;
use App\Entity\FundingCycle;
use App\Entity\FundingOrganization;
use App\Entity\Person;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Entity\FundingOrganization.
 */
class FundingOrganizationTest extends TestCase
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
    protected static $testFundingCycles;

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
     * Property to hold a creator to use for testing.
     *
     * @var Person $testCreator
     */
    protected $testCreator;

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
     * Property to hold a set of PersonFundingOrganizations for testing.
     *
     * @var $testPersonFundingOrganizations
     */
    protected $testPersonFundingOrganizations;

    /**
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of FundingOrganization.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->testCreator = new Person;
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
        $this->fundingOrganization->setCreator($this->testCreator);
        $this->testPersonFundingOrganizations = array(
            \Mockery::mock(
                '\App\Entity\PersonFundingOrganization',
                array(
                    'setFundingOrganization' => null,
                )
            ),
            \Mockery::mock(
                '\App\Entity\PersonFundingOrganization',
                array(
                    'setFundingOrganization' => null,
                )
            ),
        );
        $this->fundingOrganization->setPersonFundingOrganizations($this->testPersonFundingOrganizations);

        $this->testNewPersonFundingOrganizations = array(
            \Mockery::mock(
                '\App\Entity\PersonFundingOrganization',
                array(
                    'setFundingOrganization' => null,
                )
            ),
        );

        $this->testMockFundingCycle = \Mockery::mock('\App\Entity\FundingCycle');
        $this->testMockFundingCycle->shouldReceive('setFundingOrganization');
        $this->testMockFundingCycle->shouldReceive('jsonSerialize');
        $this->fundingOrganization->setFundingCycles(array($this->testMockFundingCycle));

        $this->timeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->timeStampISO = $this->timeStamp->format(\DateTime::ISO8601);
        $this->timeStampLocalized = clone $this->timeStamp;
        $this->timeStampLocalized->setTimeZone(new \DateTimeZone(date_default_timezone_get()));
        $this->timeStampLocalizedISO = $this->timeStampLocalized->format(\DateTime::ISO8601);

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
     * Test the getFundingCycles() method.
     *
     * This method verify the return a set of Funding Cycles.
     *
     * @return void
     */
    public function testGetFundingCycles()
    {
        $fundingCycles = $this->fundingOrganization->getFundingCycles();
        foreach ($fundingCycles as $fc) {
            $this->assertInstanceOf('\App\Entity\FundingCycle', $fc);
        }
    }

    /**
     * Test the setFundingCycles() method with bad (non-FC) element.
     *
     * This method should result in an exception being thrown.
     *
     * @return void
     */
    public function testSetFundingCyclesWithNonFC()
    {
        $this->expectException(\Exception::class);
        $this->fundingOrganization->setFundingCycles('string data');
    }

    /**
     * Test the setFundingCycles() method with a bad (non-FC) element included in set.
     *
     * This method should result in an exception being thrown.
     *
     * @return void
     */
    public function testSetFundingCyclesWithNonFCElement()
    {
        $this->expectException(\Exception::class);
        $this->fundingOrganization->setFundingCycles(array($this->testMockFundingCycle, 'string data'));
    }

    /**
     * Test the getPersonFundingOrganizations method.
     *
     * Verifies the associated PersonFundingOrganizations are each an instance of PersonFundingOrganizations.
     *
     * @return void
     */
    public function testGetPersonFundingOrganization()
    {
        $personFundingOrganizations = $this->fundingOrganization->getPersonFundingOrganizations();
        foreach ($personFundingOrganizations as $personFundingOrganization) {
            $this->assertInstanceOf('\App\Entity\PersonFundingOrganization', $personFundingOrganization);
        }
    }

    /**
     * Test the setPersonFundingOrganizations() method with a non-array/traversable object.
     *
     * This method should result in an exception being thrown.
     *
     * @return void
     */
    public function testSetPersonFundingOrganizationsWithNonTraversable()
    {
        $this->expectException(\Exception::class);
        $this->fundingOrganization->setPersonFundingOrganizations('string data');
    }

    /**
     * Test setPersonFundingOrganizations() with bad (non-PersonFundingOrganization) element.
     *
     * This method should result in an exception being thrown.
     *
     * @return void
     */
    public function testSetPersonFundingOrganizationsWithANonPersonFundingOrganizationInArray()
    {
        $this->expectException(\Exception::class);
        $testArry = $this->testPersonFundingOrganizations;
        array_push($testArry, 'string data');
        $this->fundingOrganization->setPersonFundingOrganizations($testArry);
    }

    /**
     * Test getter and setter for short name attribute.
     *
     * @return void
     */
    public function testGetAndSetShortName(): void
    {
        $shortName = 'Harte Research Institute';
        $this->fundingOrganization->setShortName($shortName);
        $this->assertEquals($shortName, $this->fundingOrganization->getShortName());
    }

    public function testGetAndSetDefaultFunder(): void
    {
        $funder = new Funder();
        $funder->setName('Test');

        $this->fundingOrganization->setDefaultFunder($funder);

        $this->assertEquals($funder, $this->fundingOrganization->getDefaultFunder());
    }
}
