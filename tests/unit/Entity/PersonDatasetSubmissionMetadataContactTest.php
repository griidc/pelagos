<?php

namespace App\Tests\Unit\Entity;

use App\Entity\DatasetSubmission;
use App\Entity\Person;
use App\Entity\PersonDatasetSubmissionMetadataContact;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Entity\PersonDatasetSubmissionMetadataContact.
 */
class PersonDatasetSubmissionMetadataContactTest extends TestCase
{
    /**
     * The PersonDatasetSubmissionMetadataContact instance to be tested.
     */
    private PersonDatasetSubmissionMetadataContact $personDatasetSubmissionMetadataContact;

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
        $this->personDatasetSubmissionMetadataContact = new PersonDatasetSubmissionMetadataContact();
        $this->mockPerson = \Mockery::mock(Person::class);
        $this->mockDatasetSubmission = \Mockery::mock(DatasetSubmission::class);
    }

    /**
     * Test the getPerson and setPerson methods.
     */
    public function testGetAndSetPerson(): void
    {
        $this->personDatasetSubmissionMetadataContact->setPerson($this->mockPerson);
        $this->assertSame($this->mockPerson, $this->personDatasetSubmissionMetadataContact->getPerson());
    }

    /**
     * Test the getRole and setRole methods.
     */
    public function testGetAndSetRole(): void
    {
        $this->personDatasetSubmissionMetadataContact->setRole('author');
        $this->assertEquals('author', $this->personDatasetSubmissionMetadataContact->getRole());
    }

    /**
     * Test setting an invalid role.
     */
    public function testSetInvalidRole(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->personDatasetSubmissionMetadataContact->setRole('invalid-role');
    }

    /**
     * Test the getDatasetSubmission and setDatasetSubmission methods.
     */
    public function testGetAndSetDatasetSubmission(): void
    {
        $this->personDatasetSubmissionMetadataContact->setDatasetSubmission($this->mockDatasetSubmission);
        $this->assertSame($this->mockDatasetSubmission, $this->personDatasetSubmissionMetadataContact->getDatasetSubmission());
    }

    /**
     * Test the isPrimaryContact and setPrimaryContact methods.
     */
    public function testIsAndSetPrimaryContact(): void
    {
        $this->personDatasetSubmissionMetadataContact->setPrimaryContact(true);
        $this->assertTrue($this->personDatasetSubmissionMetadataContact->isPrimaryContact());
        $this->personDatasetSubmissionMetadataContact->setPrimaryContact(false);
        $this->assertFalse($this->personDatasetSubmissionMetadataContact->isPrimaryContact());
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
        $this->assertEquals($expectedRoles, PersonDatasetSubmissionMetadataContact::getRoleChoices());
    }

    /**
     * Test the __toString method.
     */
    public function testToString(): void
    {
        $this->mockPerson->shouldReceive('__toString')->andReturn('Person Name');
        $this->personDatasetSubmissionMetadataContact->setPerson($this->mockPerson);
        $this->assertEquals('Person Name', (string) $this->personDatasetSubmissionMetadataContact);
    }

    /**
     * Test the tear down of the test.
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }
}
