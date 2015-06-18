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
    /** @var Person $person Property to hold an instance of Person for testing */
    protected $person;

    /** @var \Symfony\Component\Validator\Validator $validator Property to hold an instance of the Symfony Validator */
    protected $validator;

    /** @var string $testCreator Static class variable containing  creator to use for testing */
    protected static $testCreator = 'tuser';

    /** @var string $testFirstName Static class variable containing a first name to use for testing */
    protected static $testFirstName = 'MyFirstName';

    /** @var string $testLastName Static class variable containing a last name to use for testing */
    protected static $testLastName = 'MyLastName';

    /** @var string $testEmailAddress Static class variable containing an email address to use for testing */
    protected static $testEmailAddress = 'foo@bar.com';

    /**
     * @var string $testInvalidEmailAddress Static class variable containing an invalid email address to use for testing
     */
    protected static $testInvalidEmailAddress = 'foo@bar@com';

    /**
     * Setup for PHPUnit tests.
     * This includes the autoloader and instantiates an instance of Person.
     */
    protected function setUp()
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();
        $this->person = new Person(
            self::$testFirstName,
            self::$testLastName,
            self::$testEmailAddress,
            self::$testCreator
        );
    }

    /**
     * Test the getId method.
     * This method should always return null because it can not be set (even by the constructor).
     * The id property can only be set when a Person is instantiated from persistence by Doctrine.
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
     * This method should return the first name that was passed to the constructor.
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
     * This method should return the last name that was passed to the constructor.
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
     * This method should return the email address that was passed to the constructor.
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
     * This method should return the creator that was passed to the constructor.
     */
    public function testGetCreator()
    {
        $this->assertEquals(
            $this->person->getCreator(),
            self::$testCreator
        );
    }

    /**
     * Test the getCreationTimeStamp method.
     * This method should return a \DateTime object localized to the current timezone.
     */
    public function testGetCreationTimeStamp()
    {
        $creationTimeStamp = $this->person->getCreationTimeStamp();
        $this->assertInstanceOf('\DateTime', $creationTimeStamp);
        $this->assertEquals(
            date_default_timezone_get(),
            $creationTimeStamp->getTimezone()->getName()
        );
    }

    /**
     * Test the getCreationTimeStamp method (non-localized).
     * This method should return a \DateTime object in UTC.
     */
    public function testGetCreationTimeStampNonLocalized()
    {
        $creationTimeStamp = $this->person->getCreationTimeStamp(false);
        $this->assertInstanceOf('\DateTime', $creationTimeStamp);
        $this->assertEquals(
            'UTC',
            $creationTimeStamp->getTimezone()->getName()
        );
    }

    /**
     * Test the setCreationTimeStamp method.
     * This method should accept a \DateTime object in UTC.
     * We should be able to get back the same timestamp in UTC
     * if we call getCreationTimeStamp(false) (non-localized).
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
     */
    public function testSetCreationTimeStampFailForNonUTC()
    {
        $this->person->setCreationTimeStamp(
            new \DateTime('now', new \DateTimeZone('America/Chicago'))
        );
    }

    /**
     * Test that validation fails for a Person with a null first name.
     */
    public function testNullFirstName()
    {
        $this->person = new Person(
            null,
            self::$testLastName,
            self::$testEmailAddress,
            self::$testCreator
        );
        $violations = $this->validator->validate($this->person);
        $this->assertCount(1, $violations);
        $this->assertInstanceOf('\Symfony\Component\Validator\ConstraintViolation', $violations[0]);
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\NotBlank', $violations[0]->getConstraint());
        $this->assertEquals('firstName', $violations[0]->getPropertyPath());
        $this->assertEquals('First name is required', $violations[0]->getMessage());
    }

    /**
     * Test that validation fails when validating a value of null against the constraints for the firstName property.
     */
    public function testValidatePropertyValueNullFirstName()
    {
        $violations = $this->validator->validatePropertyValue('\Pelagos\Entity\Person', 'firstName', null);
        $this->assertCount(1, $violations);
        $this->assertInstanceOf('\Symfony\Component\Validator\ConstraintViolation', $violations[0]);
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\NotBlank', $violations[0]->getConstraint());
        $this->assertEquals('First name is required', $violations[0]->getMessage());
    }

    /**
     * Test that validation fails for a Person with an empty first name.
     */
    public function testEmptyFirstName()
    {
        $this->person = new Person(
            '',
            self::$testLastName,
            self::$testEmailAddress,
            self::$testCreator
        );
        $violations = $this->validator->validate($this->person);
        $this->assertCount(1, $violations);
        $this->assertInstanceOf('\Symfony\Component\Validator\ConstraintViolation', $violations[0]);
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\NotBlank', $violations[0]->getConstraint());
        $this->assertEquals('firstName', $violations[0]->getPropertyPath());
        $this->assertEquals('First name is required', $violations[0]->getMessage());
    }

    /**
     * Test that validation fails when validating a value of '' against the constraints for the firstName property.
     */
    public function testValidatePropertyValueEmptyFirstName()
    {
        $violations = $this->validator->validatePropertyValue('\Pelagos\Entity\Person', 'firstName', '');
        $this->assertCount(1, $violations);
        $this->assertInstanceOf('\Symfony\Component\Validator\ConstraintViolation', $violations[0]);
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\NotBlank', $violations[0]->getConstraint());
        $this->assertEquals('First name is required', $violations[0]->getMessage());
    }

    /**
     * Test that validation fails for a Person with angle brackets in their first name.
     */
    public function testAngleBracketsInFirstName()
    {
        $this->person = new Person(
            '<i>' . self::$testFirstName . '</i>',
            self::$testLastName,
            self::$testEmailAddress,
            self::$testCreator
        );
        $violations = $this->validator->validate($this->person);
        $this->assertCount(1, $violations);
        $this->assertInstanceOf('\Symfony\Component\Validator\ConstraintViolation', $violations[0]);
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\NoAngleBrackets', $violations[0]->getConstraint());
        $this->assertEquals('firstName', $violations[0]->getPropertyPath());
        $this->assertEquals('First name cannot contain angle brackets (< or >)', $violations[0]->getMessage());
    }

    /**
     * Test that validation fails when validating a string that contains angle brackets against
     * the constraints for the firstName property.
     */
    public function testValidatePropertyValueAngleBracketsInFirstName()
    {
        $violations = $this->validator->validatePropertyValue(
            '\Pelagos\Entity\Person',
            'firstName',
            '<i>' . self::$testFirstName . '</i>'
        );
        $this->assertCount(1, $violations);
        $this->assertInstanceOf('\Symfony\Component\Validator\ConstraintViolation', $violations[0]);
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\NoAngleBrackets', $violations[0]->getConstraint());
        $this->assertEquals('First name cannot contain angle brackets (< or >)', $violations[0]->getMessage());
    }

    /**
     * Test that validation succeeds when validating a valid first name against
     * the constraints for the firstName property.
     */
    public function testValidatePropertyValueValidFirstName()
    {
        $violations = $this->validator->validatePropertyValue(
            '\Pelagos\Entity\Person',
            'firstName',
            self::$testFirstName
        );
        $this->assertCount(0, $violations);
    }

    /**
     * Test that validation fails for a Person with a null last name.
     */
    public function testNullLastName()
    {
        $this->person = new Person(
            self::$testFirstName,
            null,
            self::$testEmailAddress,
            self::$testCreator
        );
        $violations = $this->validator->validate($this->person);
        $this->assertCount(1, $violations);
        $this->assertInstanceOf('\Symfony\Component\Validator\ConstraintViolation', $violations[0]);
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\NotBlank', $violations[0]->getConstraint());
        $this->assertEquals('lastName', $violations[0]->getPropertyPath());
        $this->assertEquals('Last name is required', $violations[0]->getMessage());
    }

    /**
     * Test that validation fails when validating a value of null against the constraints for the lastName property.
     */
    public function testValidatePropertyValueNullLastName()
    {
        $violations = $this->validator->validatePropertyValue('\Pelagos\Entity\Person', 'lastName', null);
        $this->assertCount(1, $violations);
        $this->assertInstanceOf('\Symfony\Component\Validator\ConstraintViolation', $violations[0]);
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\NotBlank', $violations[0]->getConstraint());
        $this->assertEquals('Last name is required', $violations[0]->getMessage());
    }

    /**
     * Test that validation fails for a Person with an empty last name.
     */
    public function testEmptyLastName()
    {
        $this->person = new Person(
            self::$testFirstName,
            '',
            self::$testEmailAddress,
            self::$testCreator
        );
        $violations = $this->validator->validate($this->person);
        $this->assertCount(1, $violations);
        $this->assertInstanceOf('\Symfony\Component\Validator\ConstraintViolation', $violations[0]);
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\NotBlank', $violations[0]->getConstraint());
        $this->assertEquals('lastName', $violations[0]->getPropertyPath());
        $this->assertEquals('Last name is required', $violations[0]->getMessage());
    }

    /**
     * Test that validation fails when validating a value of '' against the constraints for the lastName property.
     */
    public function testValidatePropertyValueEmptyLastName()
    {
        $violations = $this->validator->validatePropertyValue('\Pelagos\Entity\Person', 'lastName', '');
        $this->assertCount(1, $violations);
        $this->assertInstanceOf('\Symfony\Component\Validator\ConstraintViolation', $violations[0]);
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\NotBlank', $violations[0]->getConstraint());
        $this->assertEquals('Last name is required', $violations[0]->getMessage());
    }

    /**
     * Test that validation fails for a Person with angle brackets in their last name.
     */
    public function testAngleBracketsInLastName()
    {
        $this->person = new Person(
            self::$testFirstName,
            '<i>' . self::$testLastName . '</i>',
            self::$testEmailAddress,
            self::$testCreator
        );
        $violations = $this->validator->validate($this->person);
        $this->assertCount(1, $violations);
        $this->assertInstanceOf('\Symfony\Component\Validator\ConstraintViolation', $violations[0]);
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\NoAngleBrackets', $violations[0]->getConstraint());
        $this->assertEquals('lastName', $violations[0]->getPropertyPath());
        $this->assertEquals('Last name cannot contain angle brackets (< or >)', $violations[0]->getMessage());
    }

    /**
     * Test that validation fails when validating a string that contains angle brackets against
     * the constraints for the lastName property.
     */
    public function testValidatePropertyValueAngleBracketsInLastName()
    {
        $violations = $this->validator->validatePropertyValue(
            '\Pelagos\Entity\Person',
            'lastName',
            '<i>' . self::$testLastName . '</i>'
        );
        $this->assertCount(1, $violations);
        $this->assertInstanceOf('\Symfony\Component\Validator\ConstraintViolation', $violations[0]);
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\NoAngleBrackets', $violations[0]->getConstraint());
        $this->assertEquals('Last name cannot contain angle brackets (< or >)', $violations[0]->getMessage());
    }

    /**
     * Test that validation succeeds when validating a valid last name against
     * the constraints for the lastName property.
     */
    public function testValidatePropertyValueValidLastName()
    {
        $violations = $this->validator->validatePropertyValue(
            '\Pelagos\Entity\Person',
            'lastName',
            self::$testLastName
        );
        $this->assertCount(0, $violations);
    }

    /**
     * Test that validation fails for a Person with a null email address.
     */
    public function testNullEmailAddress()
    {
        $this->person = new Person(
            self::$testFirstName,
            self::$testLastName,
            null,
            self::$testCreator
        );
        $violations = $this->validator->validate($this->person);
        $this->assertCount(1, $violations);
        $this->assertInstanceOf('\Symfony\Component\Validator\ConstraintViolation', $violations[0]);
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\NotBlank', $violations[0]->getConstraint());
        $this->assertEquals('emailAddress', $violations[0]->getPropertyPath());
        $this->assertEquals('Email address is required', $violations[0]->getMessage());
    }

    /**
     * Test that validation fails when validating a value of null against the constraints for the emailAddress property.
     */
    public function testValidatePropertyValueNullEmailAddress()
    {
        $violations = $this->validator->validatePropertyValue('\Pelagos\Entity\Person', 'emailAddress', null);
        $this->assertCount(1, $violations);
        $this->assertInstanceOf('\Symfony\Component\Validator\ConstraintViolation', $violations[0]);
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\NotBlank', $violations[0]->getConstraint());
        $this->assertEquals('Email address is required', $violations[0]->getMessage());
    }

    /**
     * Test that validation fails for a Person with an empty email address.
     */
    public function testEmptyEmailAddress()
    {
        $this->person = new Person(
            self::$testFirstName,
            self::$testLastName,
            '',
            self::$testCreator
        );
        $violations = $this->validator->validate($this->person);
        $this->assertCount(1, $violations);
        $this->assertInstanceOf('\Symfony\Component\Validator\ConstraintViolation', $violations[0]);
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\NotBlank', $violations[0]->getConstraint());
        $this->assertEquals('emailAddress', $violations[0]->getPropertyPath());
        $this->assertEquals('Email address is required', $violations[0]->getMessage());
    }

    /**
     * Test that validation fails for a Person with angle brackets in their email address.
     */
    public function testAngleBracketsInEmailAddress()
    {
        $this->person = new Person(
            self::$testFirstName,
            self::$testLastName,
            '<i>' . self::$testEmailAddress . '</i>',
            self::$testCreator
        );
        $violations = $this->validator->validate($this->person);
        $this->assertCount(1, $violations);
        $this->assertInstanceOf('\Symfony\Component\Validator\ConstraintViolation', $violations[0]);
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\NoAngleBrackets', $violations[0]->getConstraint());
        $this->assertEquals('emailAddress', $violations[0]->getPropertyPath());
        $this->assertEquals('Email address cannot contain angle brackets (< or >)', $violations[0]->getMessage());
    }

    /**
     * Test that validation fails when validating a string that contains angle brackets against
     * the constraints for the emailAddress property.
     */
    public function testValidatePropertyValueAngleBracketsInEmailAddress()
    {
        $violations = $this->validator->validatePropertyValue(
            '\Pelagos\Entity\Person',
            'emailAddress',
            '<i>' . self::$testEmailAddress . '</i>'
        );
        $this->assertCount(1, $violations);
        $this->assertInstanceOf('\Symfony\Component\Validator\ConstraintViolation', $violations[0]);
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\NoAngleBrackets', $violations[0]->getConstraint());
        $this->assertEquals('Email address cannot contain angle brackets (< or >)', $violations[0]->getMessage());
    }

    /**
     * Test that validation fails for a Person with an invalid address.
     */
    public function testInvalidEmailAddress()
    {
        $this->person = new Person(
            self::$testFirstName,
            self::$testLastName,
            self::$testInvalidEmailAddress,
            self::$testCreator
        );
        $violations = $this->validator->validate($this->person);
        $this->assertCount(1, $violations);
        $this->assertInstanceOf('\Symfony\Component\Validator\ConstraintViolation', $violations[0]);
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\Email', $violations[0]->getConstraint());
        $this->assertEquals('emailAddress', $violations[0]->getPropertyPath());
        $this->assertEquals('Email address is invalid', $violations[0]->getMessage());
    }

    /**
     * Test that validation fails when validating a string that contains an invalid email address against
     * the constraints for the emailAddress property.
     */
    public function testValidatePropertyValueInvalidEmailAddress()
    {
        $violations = $this->validator->validatePropertyValue(
            '\Pelagos\Entity\Person',
            'emailAddress',
            self::$testInvalidEmailAddress
        );
        $this->assertCount(1, $violations);
        $this->assertInstanceOf('\Symfony\Component\Validator\ConstraintViolation', $violations[0]);
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\Email', $violations[0]->getConstraint());
        $this->assertEquals('Email address is invalid', $violations[0]->getMessage());
    }

    /**
     * Test that validation succeeds when validating a valid email address against
     * the constraints for the emailAddress property.
     */
    public function testValidatePropertyValueValidEmailAddress()
    {
        $violations = $this->validator->validatePropertyValue(
            '\Pelagos\Entity\Person',
            'emailAddress',
            self::$testEmailAddress
        );
        $this->assertCount(0, $violations);
    }

    /**
     * Test that Person is JsonSerializable and serializes to the expected JSON.
     */
    public function testJsonSerialize()
    {
        $timeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
        $timeStampISO = $timeStamp->format(\DateTime::ISO8601);
        $personData = array(
            'id' => null,
            'firstName' => self::$testFirstName,
            'lastName' => self::$testLastName,
            'emailAddress' => self::$testEmailAddress,
            'creationTimeStamp' => $timeStampISO,
            'creator' => self::$testCreator,
        );
        $this->person->setCreationTimeStamp($timeStamp);
        $this->assertEquals(json_encode($personData), json_encode($this->person));
    }

    /**
     * Test that we can update single values in Person with update().
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
}
