<?php

namespace Pelagos\Entity;

/**
 * Unit tests for Pelagos\Entity\DataRepository.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\DataRepository
 */
class DataRepositoryTest extends \PHPUnit_Framework_TestCase
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
    protected function setUp()
    {
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
        $this->dataRepository->setCreator(self::$testCreator);

        /*
            * Testing of DataRepository - Person relationship functionality is deferred until a later commit.
            *

             $this->testPersonDataRepositories = array(
             \Mockery::mock(
                '\Pelagos\Entity\PersonDataRepository',
                array(
                    'setDataRepository' => null,
                )
              ),
              \Mockery::mock(
                '\Pelagos\Entity\PersonDataRepository',
                array(
                    'setDataRepository' => null,
                )
               ),
              );

              $this->dataRepository->setPersonDataRepositories($this->testPersonDataRepositories);

              $this->testNewPersonDataRepositories = array(
              \Mockery::mock(
                '\Pelagos\Entity\PersonDataRepository',
                array(
                    'setDataRepository' => null,
                )
              ),
              );
              */
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

    /*
            * Testing of DataRepository - Person relationship functionality is deferred until a later commit.
            *
            * Test the getPersonDataRepositories method.
            *
            * Verifies the associated PersonDataRepositories are each an instance of PersonDataRepositories.
            *
            * @return void

            public function testGetPersonDataRepository()
            {
            $personDataRepositories = $this->dataRepository->getPersonDataRepositories();
            foreach ($personDataRepositories as $personDataRepository) {
            $this->assertInstanceOf('\Pelagos\Entity\PersonDataRepository', $personDataRepository);
            }
            }
            **************************/

    /*
            * Testing of DataRepository - Person relationship functionality is deferred until a later commit.
            *
            * Test the setPersonDataRepositories() method with a non-array/traversable object.
            *
            * This method should result in an exception being thrown.
            *
            * @expectedException \Exception
            *
            * @return void

            public function testSetPersonDataRepositoriesWithNonTraversable()
            {
            $this->dataRepository->setPersonDataRepositories('string data');
            }
            */

    /*
            * Testing of DataRepository - Person relationship functionality is deferred until a later commit.
             *
             * Test setPersonDataRepositories() with bad (non-PersonDataRepository) element.
             *
             * This method should result in an exception being thrown.
             *
             * @expectedException \Exception
             *
             * @return void

              public function testSetPersonDataRepositoriesWithANonPersonDataRepositoryInArray()
              {
                  $testArry = $this->testPersonDataRepositories;
                  array_push($testArry, 'string data');
                  $this->dataRepository->setPersonDataRepositories($testArry);
              }
              */

    /**
     * Test the update method.
     *
     * @return void
     */
    public function testUpdate()
    {
        $this->dataRepository->update(
            array(
                'name' => 'new_name',
                'emailAddress' => 'new_emailAddress',
                'description' => 'new_description',
                'url' => 'new_url',
                'phoneNumber' => 'new_phoneNumber',
                'deliveryPoint' => 'new_deliveryPoint',
                'city' => 'new_city',
                'administrativeArea' => 'new_administrativeArea',
                'postalCode' => 'new_postalCode',
                'country' => 'new_country',
                'creator' => 'new_creator'
                // 'personDataRepositories' => $this->testNewPersonDataRepositories,
            )
        );
        $this->assertEquals(
            'new_name',
            $this->dataRepository->getName()
        );
        $this->assertEquals(
            'new_emailAddress',
            $this->dataRepository->getEmailAddress()
        );
        $this->assertEquals(
            'new_description',
            $this->dataRepository->getDescription()
        );
        $this->assertEquals(
            'new_url',
            $this->dataRepository->getUrl()
        );
        $this->assertEquals(
            'new_phoneNumber',
            $this->dataRepository->getPhoneNumber()
        );
        $this->assertEquals(
            'new_deliveryPoint',
            $this->dataRepository->getDeliveryPoint()
        );
        $this->assertEquals(
            'new_city',
            $this->dataRepository->getCity()
        );
        $this->assertEquals(
            'new_administrativeArea',
            $this->dataRepository->getAdministrativeArea()
        );
        $this->assertEquals(
            'new_postalCode',
            $this->dataRepository->getPostalCode()
        );
        $this->assertEquals(
            'new_country',
            $this->dataRepository->getCountry()
        );
        $this->assertEquals(
            'new_creator',
            $this->dataRepository->getCreator()
        );
        /**
         * Testing of DataRepository - Person relationship functionality
         * is deferred until a later commit.
        $this->assertSame(
            $this->testNewPersonDataRepositories,
            $this->dataRepository->getPersonDataRepositories()
        );
         * ******************/
    }

    /**
     * Test that DataRepository is JsonSerializable and serializes to the expected JSON.
     *
     * @return void
     */
    public function testJsonSerialize()
    {
        $dataRepositoryData = array(
            'id' => null,
            'creator' => self::$testCreator,
            'creationTimeStamp' => null,
            'modifier' => self::$testCreator,
            'modificationTimeStamp' => null,
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
        );
        $this->assertEquals(json_encode($dataRepositoryData), json_encode($this->dataRepository));
    }

    /**
     * Test that DataRepository can be returned as an array via asArray(arry) method.
     *
     * @return void
     */
    public function testAsArray()
    {
        $dataRepositoryProperties = array(
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
            'modifier'
        );
        $dataRepositoryData = array(
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
            self::$testCreator
        );
        $this->assertEquals(
            $dataRepositoryData,
            $this->dataRepository->asArray(
                $dataRepositoryProperties
            )
        );
    }
}
