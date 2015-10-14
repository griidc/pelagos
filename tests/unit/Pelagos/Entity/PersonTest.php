<?php

namespace Pelagos\Entity;

use Symfony\Component\Validator\Validation;

/**
 * Unit tests for Pelagos\Entity\Person.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\Person
 */
class PersonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Property to hold an instance of Person for testing.
     *
     * @var Person $person
     */
    protected $person;

    /**
     * Property to hold an instance of the Symfony Validator.
     *
     * @var \Symfony\Component\Validator\Validator $validator
     */
    protected $validator;

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
     * Static class variable containing creator to use for testing.
     *
     * @var string $testCreator
     */
    protected static $testCreator = 'tuser';

    /**
     * Static class variable containing a first name to use for testing.
     *
     * @var string $testFirstName
     */
    protected static $testFirstName = 'MyFirstName';

    /**
     * Static class variable containing a last name to use for testing.
     *
     * @var string $testLastName
     */
    protected static $testLastName = 'MyLastName';

    /**
     * Static class variable containing an email address to use for testing.
     *
     * @var string $testEmailAddress
     */
    protected static $testEmailAddress = 'foo@bar.com';

    /**
     * Static class variable containing an invalid email address to use for testing.
     *
     * @var string $testInvalidEmailAddress
     */
    protected static $testInvalidEmailAddress = 'foo@bar@com';

    /**
     * Setup for PHPUnit tests.
     *
     * This includes the autoloader and instantiates an instance of Person.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();
        $this->person = new Person;
        $this->person->setFirstName(self::$testFirstName);
        $this->person->setLastName(self::$testLastName);
        $this->person->setEmailAddress(self::$testEmailAddress);
        $this->person->setCreator(self::$testCreator);
        $this->person->setModifier(self::$testCreator);
        $this->timeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->timeStampISO = $this->timeStamp->format(\DateTime::ISO8601);
        $this->timeStampLocalized = clone $this->timeStamp;
        $this->timeStampLocalized->setTimeZone(new \DateTimeZone(date_default_timezone_get()));
        $this->timeStampLocalizedISO = $this->timeStampLocalized->format(\DateTime::ISO8601);

        $this->testPersonResearchGroups = array(
            \Mockery::mock(
                '\Pelagos\Entity\PersonResearchGroup',
                array(
                    'setPerson' => null
                )
            ),
            \Mockery::mock(
                '\Pelagos\Entity\PersonResearchGroup',
                array(
                    'setPerson' => null
                )
            ),
        );
        $this->person->setPersonResearchGroups($this->testPersonResearchGroups);
    }

    /**
     * Test the getId method.
     *
     * This method should always return null because it can not be set (even by the constructor).
     * The id property can only be set when a Person is instantiated from persistence by Doctrine.
     *
     * @return void
     */
    public function testGetID()
    {
        $this->assertEquals(
            $this->person->getId(),
            null
        );
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
     * Test the getCreator method.
     *
     * This method should return the creator that was set in setUp.
     *
     * @return void
     */
    public function testGetCreator()
    {
        $this->assertEquals(
            $this->person->getCreator(),
            self::$testCreator
        );
    }

    /**
     * Test the getModifier method.
     *
     * This method should return the modifier that was set in setUp by setCreator (which also sets the modifier).
     *
     * @return void
     */
    public function testGetModifier()
    {
        $this->assertEquals(
            $this->person->getModifier(),
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
        $this->person->setCreationTimeStamp($timeStamp);
        $creationTimeStamp = $this->person->getCreationTimeStamp(false);
        $this->assertInstanceOf('\DateTime', $creationTimeStamp);
        $this->assertEquals($timeStampISO, $creationTimeStamp->format(\DateTime::ISO8601));
    }

    /**
     * Test the setCreationTimeStamp method with a non-UTC timestamp.
     *
     * @expectedException \Exception
     *
     * @return void
     */
    public function testSetCreationTimeStampFailForNonUTC()
    {
        $this->person->setCreationTimeStamp(
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
        $this->person->setCreationTimeStamp($this->timeStamp);
        $creationTimeStamp = $this->person->getCreationTimeStamp();
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
        $this->person->setCreationTimeStamp($this->timeStamp);
        $creationTimeStamp = $this->person->getCreationTimeStamp(true);
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
        $this->person->setCreationTimeStamp($this->timeStamp);
        $this->assertEquals(
            $this->timeStampISO,
            $this->person->getCreationTimeStampAsISO()
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
        $this->person->setCreationTimeStamp($this->timeStamp);
        $this->assertEquals(
            $this->timeStampLocalizedISO,
            $this->person->getCreationTimeStampAsISO(true)
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
        $this->assertNull($this->person->getCreationTimeStampAsISO());
    }

    /**
     * Test the setModificationTimeStamp method.
     *
     * This method should accept a \DateTime object in UTC.
     * We should be able to get back the same timestamp in UTC
     * if we call getModificationTimeStamp(false) (non-localized).
     *
     * @return void
     */
    public function testSetModificationTimeStamp()
    {
        $timeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
        $timeStampISO = $timeStamp->format(\DateTime::ISO8601);
        $this->person->setModificationTimeStamp($timeStamp);
        $modificationTimeStamp = $this->person->getModificationTimeStamp(false);
        $this->assertInstanceOf('\DateTime', $modificationTimeStamp);
        $this->assertEquals($timeStampISO, $modificationTimeStamp->format(\DateTime::ISO8601));
    }

    /**
     * Test the setModificationTimeStamp method with a non-UTC timestamp.
     *
     * @expectedException \Exception
     *
     * @return void
     */
    public function testSetModificationTimeStampFailForNonUTC()
    {
        $this->person->setModificationTimeStamp(
            new \DateTime('now', new \DateTimeZone('America/Chicago'))
        );
    }

    /**
     * Test the getModificationTimeStamp method.
     *
     * This method should return a \DateTime object in UTC.
     *
     * @return void
     */
    public function testGetModificationTimeStamp()
    {
        $this->person->setModificationTimeStamp($this->timeStamp);
        $modificationTimeStamp = $this->person->getModificationTimeStamp();
        $this->assertInstanceOf('\DateTime', $modificationTimeStamp);
        $this->assertEquals(
            'UTC',
            $modificationTimeStamp->getTimezone()->getName()
        );
        $this->assertEquals($this->timeStamp, $modificationTimeStamp);
    }

    /**
     * Test the getModificationTimeStamp method (localized).
     *
     * This method should return a \DateTime object localized to the current timezone.
     *
     * @return void
     */
    public function testGetModificationTimeStampLocalized()
    {
        $this->person->setModificationTimeStamp($this->timeStamp);
        $modificationTimeStamp = $this->person->getModificationTimeStamp(true);
        $this->assertInstanceOf('\DateTime', $modificationTimeStamp);
        $this->assertEquals(
            date_default_timezone_get(),
            $modificationTimeStamp->getTimezone()->getName()
        );
        $this->assertEquals($this->timeStamp, $modificationTimeStamp);
    }

    /**
     * Test the getModificationTimeStampAsISO method.
     *
     * This method should return a string containing the ISO 8601 representation
     * of the creation time stamp localized to the current timezone.
     *
     * @return void
     */
    public function testGetModificationTimeStampAsISO()
    {
        $this->person->setModificationTimeStamp($this->timeStamp);
        $this->assertEquals(
            $this->timeStampISO,
            $this->person->getModificationTimeStampAsISO()
        );
    }

    /**
     * Test the getModificationTimeStampAsISO method.
     *
     * This method should return a string containing the ISO 8601 representation
     * of the creation time stamp localized to the current timezone.
     *
     * @return void
     */
    public function testGetModificationTimeStampAsISOLocalized()
    {
        $this->person->setModificationTimeStamp($this->timeStamp);
        $this->assertEquals(
            $this->timeStampLocalizedISO,
            $this->person->getModificationTimeStampAsISO(true)
        );
    }

    /**
     * Test the getModificationTimeStampAsISO method when creationTimeStamp is null.
     *
     * This method should return null in this case.
     *
     * @return void
     */
    public function testGetModificationTimeStampAsISONull()
    {
        $this->assertNull($this->person->getModificationTimeStampAsISO());
    }

    /**
     * Test that updateTimeStamps sets both creationTimeStamp and modificationTimeStamp.
     *
     * @return void
     */
    public function testUpdateTimeStamps()
    {
        $this->assertNull($this->person->getCreationTimeStamp());
        $this->assertNull($this->person->getModificationTimeStamp());
        $this->person->updateTimeStamps();
        $this->assertInstanceOf('\DateTime', $this->person->getCreationTimeStamp());
        $this->assertInstanceOf('\DateTime', $this->person->getModificationTimeStamp());
    }

    /**
     * Test that validation fails for a Person with a null first name.
     *
     * @return void
     */
    public function testNullFirstName()
    {
        $this->person->setFirstName(null);
        $violations = $this->validator->validateProperty($this->person, 'firstName');
        $this->assertCount(1, $violations);
        $this->assertInstanceOf(
            '\Symfony\Component\Validator\ConstraintViolation',
            $violations[0]
        );
        $this->assertInstanceOf(
            '\Symfony\Component\Validator\Constraints\NotBlank',
            $violations[0]->getConstraint()
        );
        $this->assertEquals('firstName', $violations[0]->getPropertyPath());
        $this->assertEquals('First name is required', $violations[0]->getMessage());
    }

    /**
     * Test that validation fails for a Person with an empty first name.
     *
     * @return void
     */
    public function testEmptyFirstName()
    {
        $this->person->setFirstName('');
        $violations = $this->validator->validateProperty($this->person, 'firstName');
        $this->assertCount(1, $violations);
        $this->assertInstanceOf(
            '\Symfony\Component\Validator\ConstraintViolation',
            $violations[0]
        );
        $this->assertInstanceOf(
            '\Symfony\Component\Validator\Constraints\NotBlank',
            $violations[0]->getConstraint()
        );
        $this->assertEquals('firstName', $violations[0]->getPropertyPath());
        $this->assertEquals('First name is required', $violations[0]->getMessage());
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
            '\Symfony\Component\Validator\Constraints\NoAngleBrackets',
            $violations[0]->getConstraint()
        );
        $this->assertEquals('firstName', $violations[0]->getPropertyPath());
        $this->assertEquals('First name cannot contain angle brackets (< or >)', $violations[0]->getMessage());
    }

    /**
     * Test that validation fails for a Person with a null last name.
     *
     * @return void
     */
    public function testNullLastName()
    {
        $this->person->setLastName(null);
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
            '\Symfony\Component\Validator\Constraints\NoAngleBrackets',
            $violations[0]->getConstraint()
        );
        $this->assertEquals('lastName', $violations[0]->getPropertyPath());
        $this->assertEquals('Last name cannot contain angle brackets (< or >)', $violations[0]->getMessage());
    }

    /**
     * Test that validation fails for a Person with a null email address.
     *
     * @return void
     */
    public function testNullEmailAddress()
    {
        $this->person->setEmailAddress(null);
        $violations = $this->validator->validateProperty($this->person, 'emailAddress');
        $this->assertCount(1, $violations);
        $this->assertInstanceOf(
            '\Symfony\Component\Validator\ConstraintViolation',
            $violations[0]
        );
        $this->assertInstanceOf(
            '\Symfony\Component\Validator\Constraints\NotBlank',
            $violations[0]->getConstraint()
        );
        $this->assertEquals('emailAddress', $violations[0]->getPropertyPath());
        $this->assertEquals('Email address is required', $violations[0]->getMessage());
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
        $this->assertCount(1, $violations);
        $this->assertInstanceOf(
            '\Symfony\Component\Validator\ConstraintViolation',
            $violations[0]
        );
        $this->assertInstanceOf(
            '\Symfony\Component\Validator\Constraints\NotBlank',
            $violations[0]->getConstraint()
        );
        $this->assertEquals('emailAddress', $violations[0]->getPropertyPath());
        $this->assertEquals('Email address is required', $violations[0]->getMessage());
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
        $this->assertCount(1, $violations);
        $this->assertInstanceOf(
            '\Symfony\Component\Validator\ConstraintViolation',
            $violations[0]
        );
        $this->assertInstanceOf(
            '\Symfony\Component\Validator\Constraints\NoAngleBrackets',
            $violations[0]->getConstraint()
        );
        $this->assertEquals('emailAddress', $violations[0]->getPropertyPath());
        $this->assertEquals('Email address cannot contain angle brackets (< or >)', $violations[0]->getMessage());
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
        $this->assertCount(1, $violations);
        $this->assertInstanceOf(
            '\Symfony\Component\Validator\ConstraintViolation',
            $violations[0]
        );
        $this->assertInstanceOf(
            '\Symfony\Component\Validator\Constraints\Email',
            $violations[0]->getConstraint()
        );
        $this->assertEquals('emailAddress', $violations[0]->getPropertyPath());
        $this->assertEquals('Email address is invalid', $violations[0]->getMessage());
    }

    /**
     * Test that Person is JsonSerializable and serializes to the expected JSON.
     *
     * @return void
     */
    public function testJsonSerialize()
    {
        $timeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
        $timeStampISO = $timeStamp->format(\DateTime::ISO8601);
        $personData = array(
            'id' => null,
            'creator' => self::$testCreator,
            'creationTimeStamp' => $timeStampISO,
            'modifier' => self::$testCreator,
            'modificationTimeStamp' => $timeStampISO,
            'firstName' => self::$testFirstName,
            'lastName' => self::$testLastName,
            'emailAddress' => self::$testEmailAddress
        );
        $this->person->setCreationTimeStamp($timeStamp);
        $this->assertEquals(json_encode($personData), json_encode($this->person));
    }

    /**
     * Test that we can update single values in Person with update().
     *
     * @return void
     */
    public function testUpdateSingleValue()
    {
        $this->person->update(array('firstName' => 'newFirstName'));
        $this->assertEquals('newFirstName', $this->person->getFirstName());
        $this->person->update(array('lastName' => 'newLastName'));
        $this->assertEquals('newLastName', $this->person->getLastName());
        $this->person->update(array('emailAddress' => 'newEmailAddress'));
        $this->assertEquals('newEmailAddress', $this->person->getEmailAddress());
        $this->person->update(array('creator' => 'newCreator'));
        $this->assertEquals('newCreator', $this->person->getCreator());
    }

    /**
     * Test that we can update multiple values at once in Person with update().
     *
     * @return void
     */
    public function testUpdateMultipleValues()
    {
        $this->person->update(
            array(
                'firstName' => 'newFirstName2',
                'lastName' => 'newLastName2',
                'emailAddress' => 'newEmailAddress2',
                'creator' => 'newCreator2',
            )
        );
        $this->assertEquals('newFirstName2', $this->person->getFirstName());
        $this->assertEquals('newLastName2', $this->person->getLastName());
        $this->assertEquals('newEmailAddress2', $this->person->getEmailAddress());
        $this->assertEquals('newCreator2', $this->person->getCreator());
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
        $personResearchGroups = $this->person->getPersonResearchGroups();
        foreach ($personResearchGroups as $personResearchGroup) {
            $this->assertInstanceOf('\Pelagos\Entity\PersonResearchGroup', $personResearchGroup);
        }
    }

    /**
     * Test the testSetPersonResearchGroups() method with a non-array/traversable object.
     *
     * This method should result in an exception being thrown.
     *
     * @expectedException \Exception
     *
     * @return void
     */
    public function testSetPersonResearchGroupsWithNonTraversable()
    {
        $this->person->setPersonResearchGroups('string data');
    }

    /**
     * Test testSetPersonResearchGroups() method with bad (non-PersonResearchGroup) element in otherwise good array.
     *
     * This method should result in an exception being thrown.
     *
     * @expectedException \Exception
     *
     * @return void
     */
    public function testSetPersonResearchGroupsWithANonPersonResearchGroupInArray()
    {
        $testArry = $this->testPersonResearchGroups;
        array_push($testArry, 'string data');
        $this->person->setPersonResearchGroups($testArry);
    }

    /**
     * Test that we can get a Person's properties as an array.
     *
     * @return void
     */
    public function testAsArray()
    {
        // Get all properties.
        $this->assertEquals(
            array(
                null,
                self::$testCreator,
                null,
                self::$testCreator,
                null,
                self::$testFirstName,
                self::$testLastName,
                self::$testEmailAddress,
            ),
            $this->person->asArray(
                array(
                    'id',
                    'creator',
                    'creationTimeStamp',
                    'modifier',
                    'modificationTimeStamp',
                    'firstName',
                    'lastName',
                    'emailAddress',
                )
            )
        );
        // Get a subset of the properties.
        $this->assertEquals(
            array(
                self::$testFirstName,
                self::$testLastName,
                self::$testEmailAddress,
            ),
            $this->person->asArray(
                array(
                    'firstName',
                    'lastName',
                    'emailAddress',
                )
            )
        );
        // Should always come back in the order specified in the properties array.
        $this->assertEquals(
            array(
                self::$testEmailAddress,
                self::$testLastName,
                self::$testFirstName,
            ),
            $this->person->asArray(
                array(
                    'emailAddress',
                    'lastName',
                    'firstName',
                )
            )
        );
    }
}
