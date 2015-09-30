<?php

namespace Pelagos\Entity;

/**
 * Unit tests for Pelagos\Entity\PersonResearchGroup.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\PersonResearchGroup
 */
class PersonResearchGroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Property to hold an instance of PersonResearchGroup for testing.
     *
     * @var PersonResearchGroup $personResearchGroup
     */
    protected $personResearchGroup;

    /**
     * Static class variable containing a label to use for testing.
     *
     * @var string $testLabel
     */
    protected static $testLabel = 'Dictator';

    /**
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of PersonResearchGroup.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->mockPerson = \Mockery::mock('\Pelagos\Entity\Person');
        $this->mockResearchGroup = \Mockery::mock('\Pelagos\Entity\ResearchGroup');
        $this->mockResearchGroupRole = \Mockery::mock('\Pelagos\Entity\ResearchGroupRole');
        $this->personResearchGroup = new PersonResearchGroup;
        $this->personResearchGroup->setPerson($this->mockPerson);
        $this->personResearchGroup->setResearchGroup($this->mockResearchGroup);
        $this->personResearchGroup->setRole($this->mockResearchGroupRole);
        $this->personResearchGroup->setLabel(self::$testLabel);
    }

    /**
     * Test the testGetPerson() method.
     *
     * This method verify the return of the parent's Funding Cycle
     *
     * @return void
     */
    public function testGetPerson()
    {
        $this->assertInstanceOf(
            '\Pelagos\Entity\Person',
            $this->personResearchGroup->getPerson()
        );
    }

    /**
     * Test the testSetPerson() method with a non object.
     *
     * This method should result in an exception being thrown.
     *
     * @expectedException \PHPUnit_Framework_Error
     *
     * @return void
     */
    public function testSetPersonWithNonObject()
    {
        $this->personResearchGroup->setPerson('string data');
    }

    /**
     * Test the testSetPerson() method with a non Person.
     *
     * This method should result in an exception being thrown.
     *
     * @expectedException \PHPUnit_Framework_Error
     *
     * @return void
     */
    public function testSetPersonWithNonPerson()
    {
        $this->personResearchGroup->setPerson($this->mockResearchGroup);
    }

    /**
     * Test the testGetResearchGroup() method.
     *
     * This method verify the return of the parent's Funding Cycle
     *
     * @return void
     */
    public function testGetResearchGroup()
    {
        $this->assertInstanceOf(
            '\Pelagos\Entity\ResearchGroup',
            $this->personResearchGroup->getResearchGroup()
        );
    }

    /**
     * Test the testSetResearchGroup() method with a non object.
     *
     * This method should result in an exception being thrown.
     *
     * @expectedException \PHPUnit_Framework_Error
     *
     * @return void
     */
    public function testSetResearchGroupWithNonObject()
    {
        $this->personResearchGroup->setResearchGroup('string data');
    }

    /**
     * Test the testSetResearchGroup() method with a non ResearchGroup.
     *
     * This method should result in an exception being thrown.
     *
     * @expectedException \PHPUnit_Framework_Error
     *
     * @return void
     */
    public function testSetResearchGroupWithNonResearchGroup()
    {
        $this->personResearchGroup->setResearchGroup($this->mockPerson);
    }

    /**
     * Test the testGetRole() method.
     *
     * This method verify the return of the parent's Funding Cycle
     *
     * @return void
     */
    public function testGetRole()
    {
        $this->assertInstanceOf(
            '\Pelagos\Entity\ResearchGroupRole',
            $this->personResearchGroup->getRole()
        );
    }

    /**
     * Test the testSetRole() method with a non object.
     *
     * This method should result in an exception being thrown.
     *
     * @expectedException \PHPUnit_Framework_Error
     *
     * @return void
     */
    public function testSetRoleWithNonObject()
    {
        $this->personResearchGroup->setRole('string data');
    }

    /**
     * Test the testSetResearchGroup() method with a non ResearchGroup.
     *
     * This method should result in an exception being thrown.
     *
     * @expectedException \PHPUnit_Framework_Error
     *
     * @return void
     */
    public function testSetRoleWithNonResearchGroupRole()
    {
        $this->personResearchGroup->setRole($this->mockPerson);
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
            $this->personResearchGroup->getLabel()
        );
    }
}
