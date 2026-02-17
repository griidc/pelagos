<?php

namespace App\Tests\Unit\Entity;

use App\Entity\DatasetSubmission;
use App\Entity\Person;
use App\Entity\PersonDatasetSubmissionDatasetContact;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Entity\PersonDatasetSubmissionDatasetContact.
 */
class PersonDatasetSubmissionDatasetContactTest extends TestCase
{
    /**
     * The PersonDatasetSubmissionDatasetContact instance to be tested.
     */
    private PersonDatasetSubmissionDatasetContact $personDatasetSubmissionDatasetContact;

    /**
     * A mock Person object.
     * @var Person|\Mockery\MockInterface
     */
    private $mockPerson;

    /**
     * A mock DatasetSubmission object.
     * @var DatasetSubmission|\Mockery\MockInterface
     */
    private $mockDatasetSubmission;

    /**
     * Test setup.
     */
    protected function setUp(): void
    {
        $this->personDatasetSubmissionDatasetContact = new PersonDatasetSubmissionDatasetContact();
        $this->mockPerson = \Mockery::mock(Person::class);
        $this->mockDatasetSubmission = \Mockery::mock(DatasetSubmission::class);
    }

    /**
     * Test that the constructor sets primaryContact to false.
     */
    public function testConstructor(): void
    {
        $this->assertFalse($this->personDatasetSubmissionDatasetContact->isPrimaryContact());
    }

    /**
     * Test the getPerson and setPerson methods.
     */
    public function testGetAndSetPerson(): void
    {
        $this->personDatasetSubmissionDatasetContact->setPerson($this->mockPerson);
        $this->assertSame($this->mockPerson, $this->personDatasetSubmissionDatasetContact->getPerson());
    }

    /**
     * Test the getRole and setRole methods.
     */
    public function testGetAndSetRole(): void
    {
        $this->personDatasetSubmissionDatasetContact->setRole('author');
        $this->assertEquals('author', $this->personDatasetSubmissionDatasetContact->getRole());
    }

    /**
     * Test setting an invalid role.
     */
    public function testSetInvalidRole(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->personDatasetSubmissionDatasetContact->setRole('invalid-role');
    }

    /**
     * Test the getDatasetSubmission and setDatasetSubmission methods.
     */
    public function testGetAndSetDatasetSubmission(): void
    {
        $this->personDatasetSubmissionDatasetContact->setDatasetSubmission($this->mockDatasetSubmission);
        $this->assertSame($this->mockDatasetSubmission, $this->personDatasetSubmissionDatasetContact->getDatasetSubmission());
    }

    /**
     * Test the isPrimaryContact and setPrimaryContact methods.
     */
    public function testIsAndSetPrimaryContact(): void
    {
        $this->personDatasetSubmissionDatasetContact->setPrimaryContact(true);
        $this->assertTrue($this->personDatasetSubmissionDatasetContact->isPrimaryContact());
        $this->personDatasetSubmissionDatasetContact->setPrimaryContact(false);
        $this->assertFalse($this->personDatasetSubmissionDatasetContact->isPrimaryContact());
    }

    /**
     * Test the getRoleChoices static method.
     */
    public function testGetRoleChoices(): void
    {
        $expectedRoles = [
            'Point of Contact' => 'pointOfContact',
            'Principal Investigator' => 'principalInvestigator',
            'Author' => 'author',
        ];
        $this->assertEquals($expectedRoles, PersonDatasetSubmissionDatasetContact::getRoleChoices());
    }

    /**
     * Test the __toString method.
     */
    public function testToString(): void
    {
        $this->mockPerson->shouldReceive('__toString')->andReturn('Person Name');
        $this->personDatasetSubmissionDatasetContact->setPerson($this->mockPerson);
        $this->assertEquals('Person Name', (string) $this->personDatasetSubmissionDatasetContact);
    }

    /**
     * Test the tear down of the test.
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }
}
