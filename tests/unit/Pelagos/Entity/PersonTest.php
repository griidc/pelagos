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

    protected $validator;

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
            self::$testEmailAddress
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
     * Test that validation fails for a Person with a null first name.
     */
    public function testNullFirstName()
    {
        $this->person = new Person(
            null,
            self::$testLastName,
            self::$testEmailAddress
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
            self::$testEmailAddress
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
            self::$testEmailAddress
        );
        $violations = $this->validator->validate($this->person);
        $this->assertCount(1, $violations);
        $this->assertInstanceOf('\Symfony\Component\Validator\ConstraintViolation', $violations[0]);
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\Regex', $violations[0]->getConstraint());
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
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\Regex', $violations[0]->getConstraint());
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
            self::$testEmailAddress
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
            self::$testEmailAddress
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
            self::$testEmailAddress
        );
        $violations = $this->validator->validate($this->person);
        $this->assertCount(1, $violations);
        $this->assertInstanceOf('\Symfony\Component\Validator\ConstraintViolation', $violations[0]);
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\Regex', $violations[0]->getConstraint());
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
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\Regex', $violations[0]->getConstraint());
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
            null
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
            ''
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
            '<i>' . self::$testEmailAddress . '</i>'
        );
        $violations = $this->validator->validate($this->person);
        $this->assertCount(1, $violations);
        $this->assertInstanceOf('\Symfony\Component\Validator\ConstraintViolation', $violations[0]);
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\Regex', $violations[0]->getConstraint());
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
        $this->assertInstanceOf('\Symfony\Component\Validator\Constraints\Regex', $violations[0]->getConstraint());
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
            self::$testInvalidEmailAddress
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

}
