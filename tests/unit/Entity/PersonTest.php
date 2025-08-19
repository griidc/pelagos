<?php

namespace App\Tests\Entity;

use App\Entity\Person;
use App\Tests\Helpers\ValidationAssertions;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Error;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Validation;

/**
 * Unit tests for App\Entity\Person.
 */
class PersonTest extends TestCase
{
    use ValidationAssertions;

    /**
     * Property to hold an instance of Person for testing.
     * @var Person
     */
    protected $person;

    /**
     * Property to hold an instance of the Symfony Validator.
     * @var \Symfony\Component\Validator\Validator
     */
    protected $validator;

    /**
     * Static class variable containing a first name to use for testing.
     * @var string
     */
    protected static $testFirstName = 'MyFirstName';

    /**
     * Static class variable containing a last name to use for testing.
     * @var string
     */
    protected static $testLastName = 'MyLastName';

    /**
     * Static class variable containing an email address to use for testing.
     * @var string
     */
    protected static $testEmailAddress = 'foo@bar.com';

    /**
     * Static class variable containing an invalid email address to use for testing.
     * @var string
     */
    protected static $testInvalidEmailAddress = 'foo@bar@com';

    /**
     * Static class variable containing a phone number to use for testing.
     * @var string
     */
    protected static $testPhoneNumber = '555-555-5555';

    /**
     * Static class variable containing a delivery point to use for testing.
     * @var string
     */
    protected static $testDeliveryPoint = '6300 Ocean Dr.';

    /**
     * Static class variable containing a city to use for testing.
     * @var string
     */
    protected static $testCity = 'Corpus Christi';

    /**
     * Static class variable containing an administrative area to use for testing.
     * @var string
     */
    protected static $testAdministrativeArea = 'Texas';

    /**
     * Static class variable containing a postal code to use for testing.
     * @var string
     */
    protected static $testPostalCode = '78412';

    /**
     * Static class variable containing a country to use for testing.
     * @var string
     */
    protected static $testCountry = 'USA';

    /**
     * Static class variable containing a URL to use for testing.
     * @var string
     */
    protected static $testUrl = 'http://gulfresearchinitiative.org';

    /**
     * Static class variable containing an organization to use for testing.
     * @var string
     */
    protected static $testOrganization = 'Brawndo Corporation';

    /**
     * Static class variable containing a position to use for testing.
     * @var string
     */
    protected static $testPosition = 'Rehabilitation Officer';

    protected $testToken;
    protected $mockAccount;

    /**
     * Setup for PHPUnit tests.
     *
     * This includes the autoloader and instantiates an instance of Person.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping(true)
            ->getValidator();
        $this->person = new Person;
        $this->person->setFirstName(self::$testFirstName);
        $this->person->setLastName(self::$testLastName);
        $this->person->setEmailAddress(self::$testEmailAddress);
        $this->person->setPhoneNumber(self::$testPhoneNumber);
        $this->person->setDeliveryPoint(self::$testDeliveryPoint);
        $this->person->setCity(self::$testCity);
        $this->person->setAdministrativeArea(self::$testAdministrativeArea);
        $this->person->setPostalCode(self::$testPostalCode);
        $this->person->setCountry(self::$testCountry);
        $this->person->setUrl(self::$testUrl);
        $this->person->setOrganization(self::$testOrganization);
        $this->person->setPosition(self::$testPosition);
        $this->mockAccount = \Mockery::mock(
            '\App\Entity\Account',
            array(
                'setPerson' => null,
                'getPerson' => null,
            )
        );
        $this->person->setAccount($this->mockAccount);
        $this->testToken = \Mockery::mock(
            '\App\Entity\PersonToken',
            array(
                'setPerson' => null,
                'getPerson' => null,
            )
        );
        $this->person->setToken($this->testToken);
    }

    /**
     * Test the getFirstName method.
     *
     * This method should return the first name that was set in setUp.
     *
     * @return void
     */
    public function testGetFirstName()
    {
        $this->assertEquals(
            $this->person->getFirstName(),
            self::$testFirstName
        );
    }

    /**
     * Test the getLastName method.
     *
     * This method should return the last name that was set in setUp.
     *
     * @return void
     */
    public function testGetLastName()
    {
        $this->assertEquals(
            $this->person->getLastName(),
            self::$testLastName
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
            $this->person->getEmailAddress(),
            self::$testEmailAddress
        );
    }

    /**
     * Test that validation fails for a Person with angle brackets in their first name.
     *
     * @return void
     */
    public function testAngleBracketsInFirstName()
    {
        $this->person->setFirstName('<i>' . self::$testFirstName . '</i>');
        $violations = $this->validator->validateProperty($this->person, 'firstName');
        $this->assertCount(1, $violations);
        $this->assertInstanceOf(
            '\Symfony\Component\Validator\ConstraintViolation',
            $violations[0]
        );
        $this->assertInstanceOf(
            Constraints\Regex::class,
            $violations[0]->getConstraint()
        );
        $this->assertEquals('firstName', $violations[0]->getPropertyPath());
        $this->assertEquals('First name cannot contain angle brackets (< or >)', $violations[0]->getMessage());
    }

    /**
     * Test that validation fails for a Person with an empty last name.
     *
     * @return void
     */
    public function testEmptyLastName()
    {
        $this->person->setLastName('');
        $violations = $this->validator->validateProperty($this->person, 'lastName');
        $this->assertCount(1, $violations);
        $this->assertInstanceOf(
            '\Symfony\Component\Validator\ConstraintViolation',
            $violations[0]
        );
        $this->assertInstanceOf(
            '\Symfony\Component\Validator\Constraints\NotBlank',
            $violations[0]->getConstraint()
        );
        $this->assertEquals('lastName', $violations[0]->getPropertyPath());
        $this->assertEquals('Last name is required', $violations[0]->getMessage());
    }

    /**
     * Test that validation fails for a Person with angle brackets in their last name.
     *
     * @return void
     */
    public function testAngleBracketsInLastName()
    {
        $this->person->setLastName('<i>' . self::$testLastName . '</i>');
        $violations = $this->validator->validateProperty($this->person, 'lastName');
        $this->assertCount(1, $violations);
        $this->assertInstanceOf(
            '\Symfony\Component\Validator\ConstraintViolation',
            $violations[0]
        );
        $this->assertInstanceOf(
            Constraints\Regex::class,
            $violations[0]->getConstraint()
        );
        $this->assertEquals('lastName', $violations[0]->getPropertyPath());
        $this->assertEquals('Last name cannot contain angle brackets (< or >)', $violations[0]->getMessage());
    }

    /**
     * Test that validation fails for a Person with an empty email address.
     *
     * @return void
     */
    public function testEmptyEmailAddress()
    {
        $this->person->setEmailAddress('');
        $violations = $this->validator->validateProperty($this->person, 'emailAddress');
        $this->assertContainsConstraintForProperty(
            $violations,
            'emailAddress',
            Constraints\NotBlank::class,
            'Email address is required'
        );
    }

    /**
     * Test that validation fails for a Person with angle brackets in their email address.
     *
     * @return void
     */
    public function testAngleBracketsInEmailAddress()
    {
        $this->person->setEmailAddress('<i>' . self::$testEmailAddress . '</i>');
        $violations = $this->validator->validateProperty($this->person, 'emailAddress');
        $this->assertContainsConstraintForProperty(
            $violations,
            'emailAddress',
            Constraints\Regex::class,
            'Email address cannot contain angle brackets (< or >)'
        );
    }

    /**
     * Test that validation fails for a Person with an invalid address.
     *
     * @return void
     */
    public function testInvalidEmailAddress()
    {
        $this->person->setEmailAddress(self::$testInvalidEmailAddress);
        $violations = $this->validator->validateProperty($this->person, 'emailAddress');
        $this->assertContainsConstraintForProperty(
            $violations,
            'emailAddress',
            Constraints\Email::class,
            'Email address is invalid'
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
        $this->assertEquals(self::$testPhoneNumber, $this->person->getPhoneNumber());
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
        $this->assertEquals(self::$testDeliveryPoint, $this->person->getDeliveryPoint());
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
        $this->assertEquals(self::$testCity, $this->person->getCity());
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
        $this->assertEquals(self::$testAdministrativeArea, $this->person->getAdministrativeArea());
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
        $this->assertEquals(self::$testPostalCode, $this->person->getPostalCode());
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
        $this->assertEquals(self::$testCountry, $this->person->getCountry());
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
        $this->assertEquals(self::$testUrl, $this->person->getUrl());
    }

    /**
     * Test the getOrganization method.
     *
     * This method should return the organization that was set in setUp.
     *
     * @return void
     */
    public function testGetOrganization()
    {
        $this->assertEquals(self::$testOrganization, $this->person->getOrganization());
    }

    /**
     * Test the getPosition method.
     *
     * This method should return the position that was set in setUp.
     *
     * @return void
     */
    public function testGetPosition()
    {
        $this->assertEquals(self::$testPosition, $this->person->getPosition());
    }

    /**
     * Test the getAccount method.
     *
     * This method should return the same account set in setUp.
     *
     * @return void
     */
    public function testGetAccount()
    {
        $this->assertSame(
            $this->mockAccount,
            $this->person->getAccount()
        );
    }

    /**
     * Test the setAccount method with a non Account.
     *
     * This method should result in an exception being thrown.
     *
     * @return void
     */
    public function testSetAccountWithNonAccount()
    {
        $this->expectException(\TypeError::class);
        $this->person->setAccount('foo');
    }

    /**
     * Test the getToken method.
     *
     * This getToken method of person and verify it returns a Token object.
     *
     * @return void
     */
    public function testGetToken()
    {
        $this->assertSame($this->testToken, $this->person->getToken());
    }
}
