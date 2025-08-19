<?php

namespace App\Tests\Entity;

use App\Entity\DataRepository;
use App\Entity\Person;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Entity\DataRepository.
 */
class DataRepositoryTest extends TestCase
{
    /**
     * Property to hold an instance of DataRepository for testing.
     *
     * @var DataRepository $dataRepository
     */
    protected $dataRepository;

    /**
     * Static class variable containing a name to use for testing.
     *
     * @var string $testName
     */
    protected static $testName = 'My Funding Organization';

    /**
     * Static class variable containing an email address to use for testing.
     *
     * @var string $testEmailAddress
     */
    protected static $testEmailAddress = 'help@griidc.org';

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
     * Property to hold a set of PersonDataRepositories for testing.
     *
     * @var $testPersonDataRepositories
     */
    protected $testPersonDataRepositories;

    /**
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of DataRepository.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->testCreator = new Person;
        $this->dataRepository = new DataRepository;
        $this->dataRepository->setName(self::$testName);
        $this->dataRepository->setEmailAddress(self::$testEmailAddress);
        $this->dataRepository->setDescription(self::$testDescription);
        $this->dataRepository->setUrl(self::$testUrl);
        $this->dataRepository->setPhoneNumber(self::$testPhoneNumber);
        $this->dataRepository->setDeliveryPoint(self::$testDeliveryPoint);
        $this->dataRepository->setCity(self::$testCity);
        $this->dataRepository->setAdministrativeArea(self::$testAdministrativeArea);
        $this->dataRepository->setPostalCode(self::$testPostalCode);
        $this->dataRepository->setCountry(self::$testCountry);
        $this->dataRepository->setCreator($this->testCreator);
        $this->timeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->timeStampISO = $this->timeStamp->format(\DateTime::ISO8601);
        $this->timeStampLocalized = clone $this->timeStamp;
        $this->timeStampLocalized->setTimeZone(new \DateTimeZone(date_default_timezone_get()));
        $this->timeStampLocalizedISO = $this->timeStampLocalized->format(\DateTime::ISO8601);
        $this->testPersonDataRepositories = array(
            \Mockery::mock(
                '\App\Entity\PersonDataRepository',
                array(
                    'setDataRepository' => null,
                )
            ),
            \Mockery::mock(
                '\App\Entity\PersonDataRepository',
                array(
                    'setDataRepository' => null,
                )
            ),
        );
        $this->dataRepository->setPersonDataRepositories($this->testPersonDataRepositories);

        $this->newTestPersonDataRepositories = array(
            \Mockery::mock(
                '\App\Entity\PersonDataRepository',
                array(
                    'setDataRepository' => null,
                )
            ),
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
            $this->dataRepository->getName()
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
            $this->dataRepository->getEmailAddress()
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
            $this->dataRepository->getDescription()
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
            $this->dataRepository->getUrl()
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
            $this->dataRepository->getPhoneNumber()
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
            $this->dataRepository->getDeliveryPoint()
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
            $this->dataRepository->getCity()
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
            $this->dataRepository->getAdministrativeArea()
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
            $this->dataRepository->getPostalCode()
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
            $this->dataRepository->getCountry()
        );
    }

    /**
     * Test the getPersonDataRepositories method.
     *
     * Verifies the associated PersonDataRepositories are each an instance of PersonDataRepository.
     *
     * @return void
     */
    public function testGetPersonDataRepositories()
    {
        $personDataRepositories = $this->dataRepository->getPersonDataRepositories();
        foreach ($personDataRepositories as $personDataRepository) {
            $this->assertInstanceOf('\App\Entity\PersonDataRepository', $personDataRepository);
        }
    }

    /**
     * Test the setPersonDataRepositories() method with a non-array/traversable object.
     *
     * This method should result in an exception being thrown.
     *
     * @return void
     */
    public function testSetPersonDataRepositoriesWithNonTraversable()
    {
        $this->expectException(\Exception::class);
        $this->dataRepository->setPersonDataRepositories('string data');
    }

    /**
     * Test setPersonDataRepositories() with a bad (non-PersonFundingOrganization) element.
     *
     * This method should result in an exception being thrown.
     *
     * @return void
     */
    public function testSetPersonDataRepositoriesWithANonPersonDataRepositoryInArray()
    {
        $this->expectException(\Exception::class);
        $testArry = $this->testPersonDataRepositories;
        array_push($testArry, 'string data');
        $this->dataRepository->setPersonDataRepositories($testArry);
    }
}
