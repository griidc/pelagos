<?php

namespace Pelagos\Entity;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Error;

/**
 * Unit tests for Pelagos\Entity\PersonFundingOrganization.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\PersonFundingOrganization
 */
class PersonFundingOrganizationTest extends TestCase
{
    /**
     * Property to hold an instance of PersonFundingOrganization for testing.
     *
     * @var PersonFundingOrganization $personFundingOrganization
     */
    protected $personFundingOrganization;

    /**
     * Static class variable containing a label to use for testing.
     *
     * @var string $testLabel
     */
    protected static $testLabel = 'Dictator';

    /**
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of PersonFundingOrganization.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->mockPerson = \Mockery::mock('\Pelagos\Entity\Person');
        $this->mockFundingOrganization = \Mockery::mock('\Pelagos\Entity\FundingOrganization');
        $this->mockFundingOrganizationRole = \Mockery::mock('\Pelagos\Entity\FundingOrganizationRole');
        $this->personFundingOrganization = new PersonFundingOrganization;
        $this->personFundingOrganization->setPerson($this->mockPerson);
        $this->personFundingOrganization->setFundingOrganization($this->mockFundingOrganization);
        $this->personFundingOrganization->setRole($this->mockFundingOrganizationRole);
        $this->personFundingOrganization->setLabel(self::$testLabel);
    }

    /**
     * Test the getPerson method.
     *
     * This method should return the Person set in setUp.
     *
     * @return void
     */
    public function testGetPerson()
    {
        $this->assertSame(
            $this->mockPerson,
            $this->personFundingOrganization->getPerson()
        );
    }

    /**
     * Test the setPerson method with null.
     *
     * This should be allowed and null should be returned by getPerson.
     *
     * @return void
     */
    public function testSetPersonNull()
    {
        $this->personFundingOrganization->setPerson(null);
        $this->assertNull(
            $this->personFundingOrganization->getPerson()
        );
    }

    /**
     * Test the setPerson method with a non object.
     *
     * This method should result in an exception being thrown.
     *
     * @expectedException \Error
     *
     * @return void
     */
    public function testSetPersonWithNonObject()
    {
        $this->personFundingOrganization->setPerson('string data');
    }

    /**
     * Test the setPerson method with a non Person.
     *
     * This method should result in an exception being thrown.
     *
     * @expectedException \Error
     *
     * @return void
     */
    public function testSetPersonWithNonPerson()
    {
        $this->personFundingOrganization->setPerson($this->mockFundingOrganization);
    }

    /**
     * Test the getFundingOrganization method.
     *
     * This method should return the FundingOrganization set in setUp.
     *
     * @return void
     */
    public function testGetFundingOrganization()
    {
        $this->assertSame(
            $this->mockFundingOrganization,
            $this->personFundingOrganization->getFundingOrganization()
        );
    }

    /**
     * Test the setFundingOrganization method with null.
     *
     * This should be allowed and null should be returned by getFundingOrganization.
     *
     * @return void
     */
    public function testSetFundingOrganizationNull()
    {
        $this->personFundingOrganization->setFundingOrganization(null);
        $this->assertNull(
            $this->personFundingOrganization->getFundingOrganization()
        );
    }

    /**
     * Test the setFundingOrganization method with a non object.
     *
     * This method should result in an exception being thrown.
     *
     * @expectedException \Error
     *
     * @return void
     */
    public function testSetFundingOrganizationWithNonObject()
    {
        $this->personFundingOrganization->setFundingOrganization('string data');
    }

    /**
     * Test the setFundingOrganization method with a non FundingOrganization.
     *
     * This method should result in an exception being thrown.
     *
     * @expectedException \Error
     *
     * @return void
     */
    public function testSetFundingOrganizationWithNonFundingOrganization()
    {
        $this->personFundingOrganization->setFundingOrganization($this->mockPerson);
    }

    /**
     * Test the getRole method.
     *
     * This method should return the FundingOrganizationRole set in setUp.
     *
     * @return void
     */
    public function testGetRole()
    {
        $this->assertSame(
            $this->mockFundingOrganizationRole,
            $this->personFundingOrganization->getRole()
        );
    }

    /**
     * Test the setRole method with null.
     *
     * This should be allowed and null should be returned by getRole.
     *
     * @return void
     */
    public function testSetRoleNull()
    {
        $this->personFundingOrganization->setRole(null);
        $this->assertNull(
            $this->personFundingOrganization->getRole()
        );
    }

    /**
     * Test the setRole method with a non object.
     *
     * This method should result in an exception being thrown.
     *
     * @expectedException \Error
     *
     * @return void
     */
    public function testSetRoleWithNonObject()
    {
        $this->personFundingOrganization->setRole('string data');
    }

    /**
     * Test the setFundingOrganization method with a non FundingOrganization.
     *
     * This method should result in an exception being thrown.
     *
     * @expectedException \Error
     *
     * @return void
     */
    public function testSetRoleWithNonFundingOrganizationRole()
    {
        $this->personFundingOrganization->setRole($this->mockPerson);
    }

    /**
     * Test the getLabel method.
     *
     * This method should return the label that was set in setUp.
     *
     * @return void
     */
    public function testGetLabel()
    {
        $this->assertEquals(
            self::$testLabel,
            $this->personFundingOrganization->getLabel()
        );
    }

    /**
     * Test setLabel with null.
     *
     * This should be allowed and null should be returned by getLabel.
     *
     * @return void
     */
    public function testSetLabelNull()
    {
        $this->personFundingOrganization->setLabel(null);
        $this->assertNull(
            $this->personFundingOrganization->getLabel()
        );
    }

    /**
     * Test setLabel with non-string.
     *
     * This should throw an exception.
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testSetLabelNonString()
    {
        $this->personFundingOrganization->setLabel(1);
    }
}
