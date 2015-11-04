<?php

namespace Pelagos\Entity;

/**
 * Unit tests for Pelagos\Entity\PersonDataRepository.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\PersonDataRepository
 */
class PersonDataRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Property to hold an instance of PersonDataRepository for testing.
     *
     * @var PersonDataRepository $personDataRepository
     */
    protected $personDataRepository;

    /**
     * Static class variable containing a label to use for testing.
     *
     * @var string $testLabel
     */
    protected static $testLabel = 'Dictator';

    /**
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of PersonDataRepository.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->mockPerson = \Mockery::mock('\Pelagos\Entity\Person');
        $this->mockDataRepository = \Mockery::mock('\Pelagos\Entity\DataRepository');
        $this->mockDataRepositoryRole = \Mockery::mock('\Pelagos\Entity\DataRepositoryRole');
        $this->personDataRepository = new PersonDataRepository;
        $this->personDataRepository->setPerson($this->mockPerson);
        $this->personDataRepository->setDataRepository($this->mockDataRepository);
        $this->personDataRepository->setRole($this->mockDataRepositoryRole);
        $this->personDataRepository->setLabel(self::$testLabel);
    }

    /**
     * Test that the properties array is defined correctly.
     *
     * @return void
     */
    public function testProperties()
    {
        $properties = $this->personDataRepository->getProperties();

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

        $this->assertArrayHasKey('dataRepository', $properties);
        $this->assertArrayHasKey('type', $properties['dataRepository']);
        $this->assertEquals('object', $properties['dataRepository']['type']);
        $this->assertArrayHasKey('class', $properties['dataRepository']);
        $this->assertEquals('Pelagos\Entity\DataRepository', $properties['dataRepository']['class']);
        $this->assertArrayHasKey('entity', $properties['dataRepository']);
        $this->assertEquals('DataRepository', $properties['dataRepository']['entity']);
        $this->assertArrayHasKey('setter', $properties['dataRepository']);
        $this->assertEquals('setDataRepository', $properties['dataRepository']['setter']);
        $this->assertArrayHasKey('getter', $properties['dataRepository']);
        $this->assertEquals('getDataRepository', $properties['dataRepository']['getter']);

        $this->assertArrayHasKey('role', $properties);
        $this->assertArrayHasKey('type', $properties['role']);
        $this->assertEquals('object', $properties['role']['type']);
        $this->assertArrayHasKey('class', $properties['role']);
        $this->assertEquals('Pelagos\Entity\DataRepositoryRole', $properties['role']['class']);
        $this->assertArrayHasKey('entity', $properties['role']);
        $this->assertEquals('DataRepositoryRole', $properties['role']['entity']);
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
            $this->personDataRepository->getPerson()
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
        $this->personDataRepository->setPerson(null);
        $this->assertNull(
            $this->personDataRepository->getPerson()
        );
    }

    /**
     * Test the setPerson method with a non object.
     *
     * This method should result in an exception being thrown.
     *
     * @expectedException \PHPUnit_Framework_Error
     *
     * @return void
     */
    public function testSetPersonWithNonObject()
    {
        $this->personDataRepository->setPerson('string data');
    }

    /**
     * Test the setPerson method with a non Person.
     *
     * This method should result in an exception being thrown.
     *
     * @expectedException \PHPUnit_Framework_Error
     *
     * @return void
     */
    public function testSetPersonWithNonPerson()
    {
        $this->personDataRepository->setPerson($this->mockDataRepository);
    }

    /**
     * Test the getDataRepository method.
     *
     * This method should return the DataRepository set in setUp.
     *
     * @return void
     */
    public function testGetDataRepository()
    {
        $this->assertSame(
            $this->mockDataRepository,
            $this->personDataRepository->getDataRepository()
        );
    }

    /**
     * Test the setDataRepository method with null.
     *
     * This should be allowed and null should be returned by getDataRepository.
     *
     * @return void
     */
    public function testSetDataRepositoryNull()
    {
        $this->personDataRepository->setDataRepository(null);
        $this->assertNull(
            $this->personDataRepository->getDataRepository()
        );
    }

    /**
     * Test the setDataRepository method with a non object.
     *
     * This method should result in an exception being thrown.
     *
     * @expectedException \PHPUnit_Framework_Error
     *
     * @return void
     */
    public function testSetDataRepositoryWithNonObject()
    {
        $this->personDataRepository->setDataRepository('string data');
    }

    /**
     * Test the setDataRepository method with a non DataRepository.
     *
     * This method should result in an exception being thrown.
     *
     * @expectedException \PHPUnit_Framework_Error
     *
     * @return void
     */
    public function testSetDataRepositoryWithNonDataRepository()
    {
        $this->personDataRepository->setDataRepository($this->mockPerson);
    }

    /**
     * Test the getRole method.
     *
     * This method should return the DataRepositoryRole set in setUp.
     *
     * @return void
     */
    public function testGetRole()
    {
        $this->assertSame(
            $this->mockDataRepositoryRole,
            $this->personDataRepository->getRole()
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
        $this->personDataRepository->setRole(null);
        $this->assertNull(
            $this->personDataRepository->getRole()
        );
    }

    /**
     * Test the setRole method with a non object.
     *
     * This method should result in an exception being thrown.
     *
     * @expectedException \PHPUnit_Framework_Error
     *
     * @return void
     */
    public function testSetRoleWithNonObject()
    {
        $this->personDataRepository->setRole('string data');
    }

    /**
     * Test the setDataRepository method with a non DataRepository.
     *
     * This method should result in an exception being thrown.
     *
     * @expectedException \PHPUnit_Framework_Error
     *
     * @return void
     */
    public function testSetRoleWithNonDataRepositoryRole()
    {
        $this->personDataRepository->setRole($this->mockPerson);
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
            $this->personDataRepository->getLabel()
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
        $this->personDataRepository->setLabel(null);
        $this->assertNull(
            $this->personDataRepository->getLabel()
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
        $this->personDataRepository->setLabel(1);
    }
}
