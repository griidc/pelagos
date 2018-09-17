<?php

namespace Pelagos\Entity;

use PHPUnit\Framework\TestCase;

use Doctrine\Common\Collections\ArrayCollection;
use \DateTime;

/**
 * Unit tests for Pelagos\Entity\DatasetSubmissionReview.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\DatasetSubmissionReview
 *
 * @package Pelagos\Entity
 */
class DatasetSubmissionReviewTest extends TestCase
{
    /**
     * Dataset Submission Review undergoing test.
     *
     * @var DatasetSubmissionReview
     */
    protected $datasetSubmissionReview;

    /**
     * A mock DatasetSubmission.
     *
     * @var DatasetSubmission
     */
    protected $mockDatasetSubmission;

    /**
     * A mock Person.
     *
     * @var Person
     */
    protected $mockPerson;

    /**
     * Property to hold the Start Date Time for testing.
     *
     * @var \DateTime
     */
    protected $testStartDateTime;

    /**
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of DatasetSubmission and sets its properties.
     *
     * @return void
     */
    public function setUp()
    {
        $this->mockDatasetSubmission = \Mockery::mock(
            DatasetSubmission::class,
            array(
                'getStatus' => DatasetSubmission::STATUS_COMPLETE,
                'setDataset' => null,
                'getMetadataStatus' => DatasetSubmission::METADATA_STATUS_ACCEPTED,
                'getDatasetFileTransferStatus' => null,
                'getRestrictions' => null,
                'getDatasetContacts' => new ArrayCollection(
                    array(
                        \Mockery::mock(
                            PersonDatasetSubmissionDatasetContact::class,
                            array(
                                'getPerson' => \Mockery::mock(
                                    Person::class,
                                    array(
                                        'getLastName' => 'Person',
                                        'getFirstName' => 'Complete-Submission',
                                        'getEmailAddress' => 'complete-submission.person@test.null',
                                    )
                                ),
                            )
                        ),
                    )
                ),
            )
        );

        $this->mockPerson = \Mockery::mock(
            Person::class,
            array(
                'getLastName' => 'Person',
                'getFirstName' => 'Mock',
                'getEmailAddress' => 'mock.person@test.null',
            )
        );

        $this->testStartDateTime = new DateTime('now');

        $this->datasetSubmissionReview = new DatasetSubmissionReview(
            $this->mockDatasetSubmission,
            $this->mockPerson,
            $this->testStartDateTime
        );
    }

    /**
     * Test datasetSubmission getter.
     *
     * @return void
     */
    public function testCanGetDatasetSubmission()
    {
        $this->assertSame(
            $this->mockDatasetSubmission,
            $this->datasetSubmissionReview->getDatasetSubmission()
        );
    }

    /**
     * Test reviewNotes getter and setter.
     *
     * @return void
     */
    public function testCanGetAndSetReviewNotes()
    {
        $this->datasetSubmissionReview->setReviewNotes('This is a review note');
        $this->assertEquals('This is a review note', $this->datasetSubmissionReview->getReviewNotes());
    }

    /**
     * Test reviewEndDateTime getter and setter.
     *
     * @return void
     */
    public function testCanGetAndSetReviewEndDateTime()
    {
        $timeStamp = new DateTime('now', new \DateTimeZone('UTC'));
        $this->datasetSubmissionReview->setReviewEndDateTime($timeStamp);
        $this->assertEquals($timeStamp, $this->datasetSubmissionReview->getReviewEndDateTime());
    }

    /**
     * Test reviewStartDateTime getter.
     *
     * @return void
     */
    public function testCanGetReviewStartDateTime()
    {
        $this->assertSame(
            $this->testStartDateTime,
            $this->datasetSubmissionReview->getReviewStartDateTime()
        );
    }

    /**
     * Test ReviewedBy getter.
     *
     * @return void
     */
    public function testCanGetReviewedBy()
    {
        $this->assertSame(
            $this->mockPerson,
            $this->datasetSubmissionReview->getReviewedBy()
        );
    }

    /**
     * Test reviewEndedBy getter and setter.
     *
     * @return void
     */
    public function testCanGetAndSetReviewEndedBy()
    {
        $this->datasetSubmissionReview->setReviewEndedBy($this->mockPerson);
        $this->assertEquals(
            $this->mockPerson,
            $this->datasetSubmissionReview->getReviewEndedBy()
        );

        
    }
}
