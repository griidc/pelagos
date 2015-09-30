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
     * Test that the properties array is defined correctly.
     *
     * @return void
     */
    public function testProperties()
    {
        $properties = $this->personResearchGroup->getProperties();

        $this->assertArrayHasKey('person', $properties);
        $this->assertArrayHasKey('type', $properties['person']);
        $this->assertEquals('object', $properties['person']['type']);
        $this->assertArrayHasKey('class', $properties['person']);
        $this->assertEquals('Pelagos\Entity\Person', $properties['person']['class']);
        $this->assertArrayHasKey('entity', $properties['person']);
        $this->assertEquals('Person', $properties['person']['entity']);
        $this->assertArrayHasKey('setter', $properties['person']);
        $this->assertEquals('setPerson', $properties['person']['setter']);
        $this->assertArrayHasKey('getter', $properties['person']);
        $this->assertEquals('getPerson', $properties['person']['getter']);

        $this->assertArrayHasKey('researchGroup', $properties);
        $this->assertArrayHasKey('type', $properties['researchGroup']);
        $this->assertEquals('object', $properties['researchGroup']['type']);
        $this->assertArrayHasKey('class', $properties['researchGroup']);
        $this->assertEquals('Pelagos\Entity\ResearchGroup', $properties['researchGroup']['class']);
        $this->assertArrayHasKey('entity', $properties['researchGroup']);
        $this->assertEquals('ResearchGroup', $properties['researchGroup']['entity']);
        $this->assertArrayHasKey('setter', $properties['researchGroup']);
        $this->assertEquals('setResearchGroup', $properties['researchGroup']['setter']);
        $this->assertArrayHasKey('getter', $properties['researchGroup']);
        $this->assertEquals('getResearchGroup', $properties['researchGroup']['getter']);

        $this->assertArrayHasKey('role', $properties);
        $this->assertArrayHasKey('type', $properties['role']);
        $this->assertEquals('object', $properties['role']['type']);
        $this->assertArrayHasKey('class', $properties['role']);
        $this->assertEquals('Pelagos\Entity\ResearchGroupRole', $properties['role']['class']);
        $this->assertArrayHasKey('entity', $properties['role']);
        $this->assertEquals('ResearchGroupRole', $properties['role']['entity']);
        $this->assertArrayHasKey('setter', $properties['role']);
        $this->assertEquals('setRole', $properties['role']['setter']);
        $this->assertArrayHasKey('getter', $properties['role']);
        $this->assertEquals('getRole', $properties['role']['getter']);

        $this->assertArrayHasKey('label', $properties);
        $this->assertArrayHasKey('type', $properties['label']);
        $this->assertEquals('string', $properties['label']['type']);
        $this->assertArrayHasKey('setter', $properties['label']);
        $this->assertEquals('setLabel', $properties['label']['setter']);
        $this->assertArrayHasKey('getter', $properties['label']);
        $this->assertEquals('getLabel', $properties['label']['getter']);
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
