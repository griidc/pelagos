<?php

namespace App\Tests\Entity;

use App\Entity\PersonDataRepository;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Error;


/**
 * Unit tests for App\Entity\PersonDataRepository.
 */
class PersonDataRepositoryTest extends TestCase
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
    protected function setUp(): void
    {
        $this->mockPerson = \Mockery::mock('\App\Entity\Person');
        $this->mockDataRepository = \Mockery::mock('\App\Entity\DataRepository');
        $this->mockDataRepositoryRole = \Mockery::mock('\App\Entity\DataRepositoryRole');
        $this->personDataRepository = new PersonDataRepository;
        $this->personDataRepository->setPerson($this->mockPerson);
        $this->personDataRepository->setDataRepository($this->mockDataRepository);
        $this->personDataRepository->setRole($this->mockDataRepositoryRole);
        $this->personDataRepository->setLabel(self::$testLabel);
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
     * @return void
     */
    public function testSetPersonWithNonObject()
    {
        $this->expectException(\TypeError::class);
        $this->personDataRepository->setPerson('string data');
    }

    /**
     * Test the setPerson method with a non Person.
     *
     * This method should result in an exception being thrown.
     *
     * @return void
     */
    public function testSetPersonWithNonPerson()
    {
        $this->expectException(\TypeError::class);
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
     * @return void
     */
    public function testSetDataRepositoryWithNonObject()
    {
        $this->expectException(\TypeError::class);
        $this->personDataRepository->setDataRepository('string data');
    }

    /**
     * Test the setDataRepository method with a non DataRepository.
     *
     * This method should result in an exception being thrown.
     *
     * @return void
     */
    public function testSetDataRepositoryWithNonDataRepository()
    {
        $this->expectException(\TypeError::class);
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
     * @return void
     */
    public function testSetLabelNonString()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->personDataRepository->setLabel(1);
    }
}
