<?php

namespace Pelagos\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Unit tests for Pelagos\Entity\Dataset.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\Dataset
 *
 * @package Pelagos\Entity
 */
class DatasetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Dataset undergoing test.
     *
     * @var Dataset
     */
    protected $dataset;

    /**
     * Class variable containing a complete DatasetSubmission used for testing.
     *
     * @var mixed datasetSubmission
     */
    protected $mockDatasetSubmissionComplete;

    /**
     * Class variable containing a draft (incomplete) DatasetSubmission used for testing.
     *
     * @var mixed datasetSubmission
     */
    protected $mockDatasetSubmissionIncomplete;

    /**
     * Class variable containing an approved DIF used for testing.
     *
     * @var mixed mockApprovedDif
     */
    protected $mockApprovedDif;

    /**
     * Class variable containing a submitted DIF used for testing.
     *
     * @var mixed mockSubmittedDif
     */
    protected $mockSubmittedDif;

    /**
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of Dataset and sets (some of) its properties.
     *
     * @return void
     */
    public function setUp()
    {
        $this->mockDatasetSubmissionComplete = \Mockery::mock(
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
                            array (
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

        $this->mockDatasetSubmissionIncomplete = \Mockery::mock(
            DatasetSubmission::class,
            array(
                'getStatus' => DatasetSubmission::STATUS_INCOMPLETE,
                'setDataset' => null,
                'getMetadataStatus' => DatasetSubmission::METADATA_STATUS_ACCEPTED,
                'getDatasetFileTransferStatus' => null,
                'getRestrictions' => null,
                'getDatasetContacts' => new ArrayCollection(
                    array(
                        \Mockery::mock(
                            PersonDatasetSubmissionDatasetContact::class,
                            array (
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

        $this->mockDatasetSubmissionCompleteMissingContact = \Mockery::mock(
            DatasetSubmission::class,
            array(
                'getStatus' => DatasetSubmission::STATUS_COMPLETE,
                'setDataset' => null,
                'getMetadataStatus' => DatasetSubmission::METADATA_STATUS_ACCEPTED,
                'getDatasetFileTransferStatus' => null,
                'getRestrictions' => null,
                'getDatasetContacts' => new ArrayCollection(),
            )
        );

        $this->mockApprovedDif = \Mockery::mock(
            DIF::class,
            array(
                'getStatus' => DIF::STATUS_APPROVED,
                'getDataset' => null,
                'setDataset' => null,
                'getPrimaryPointOfContact' => \Mockery::mock(
                    Person::class,
                    array(
                        'getLastName' => 'Approved',
                        'getFirstName' => 'Dif',
                        'getEmailAddress' => 'approved.dif@test.null',
                    )
                ),
            )
        );
        $this->mockSubmittedDif = \Mockery::mock(
            DIF::class,
            array(
                'getStatus' => DIF::STATUS_SUBMITTED,
                'getDataset' => null,
                'setDataset' => null,
                'getPrimaryPointOfContact' => \Mockery::mock(
                    Person::class,
                    array(
                        'getLastName' => 'submitted',
                        'getFirstName' => 'Dif',
                        'getEmailAddress' => 'submitted.dif@test.null',
                    )
                ),
            )
        );

        $this->dataset = new Dataset;
    }

    /**
     * Test getPrimaryPointOfContact.
     *
     * @return void
     */
    public function testGetPrimaryPointOfContact()
    {
        // Case: We have a complete submission and an approved DIF.
        $this->dataset->setDif($this->mockApprovedDif);
        $this->dataset->setDatasetSubmission($this->mockDatasetSubmissionComplete);
        $this->assertEquals(
            'complete-submission.person@test.null',
            $this->dataset->getPrimaryPointOfContact()->getEmailAddress()
        );

        // Case: We have an incomplete submission and an approved DIF.
        $this->dataset->setDif($this->mockApprovedDif);
        $this->dataset->setDatasetSubmission($this->mockDatasetSubmissionIncomplete);
        $this->assertEquals(
            'approved.dif@test.null',
            $this->dataset->getPrimaryPointOfContact()->getEmailAddress()
        );

        // Case: We have an incomplete submission and a submitted (but not approved) DIF.
        $this->dataset->setDif($this->mockSubmittedDif);
        $this->dataset->setDatasetSubmission($this->mockDatasetSubmissionIncomplete);
        $this->assertNull(
            $this->dataset->getPrimaryPointOfContact()
        );

        // Case: We have no submission but have an approved DIF
        $this->dataset->setDif($this->mockApprovedDif);
        $this->assertEquals(
            'approved.dif@test.null',
            $this->dataset->getPrimaryPointOfContact()->getEmailAddress()
        );

        // Case: We have no submission but have a submitted (not approved) DIF.
        $this->dataset->setDif($this->mockSubmittedDif);
        $this->assertNull(
            $this->dataset->getPrimaryPointOfContact()
        );

        // Case: We have a complete submission and no DIF.
        $this->dataset->setDatasetSubmission($this->mockDatasetSubmissionComplete);
        $this->assertEquals(
            'complete-submission.person@test.null',
            $this->dataset->getPrimaryPointOfContact()->getEmailAddress()
        );

        // Case: We have an incomplete submission and no DIF.
        $this->dataset->setDatasetSubmission($this->mockDatasetSubmissionIncomplete);
        $this->assertNull(
            $this->dataset->getPrimaryPointOfContact()
        );

    }

    /**
     * Test getPrimaryPointOfContact w/bad submission (missing contact).
     *
     * @return void
     */
    public function testInvalidSubmissionMissingContact()
    {
        // Case: We have a dataset submission that dosen't have a contact.
        $this->dataset->setDif($this->mockApprovedDif);
        $this->dataset->setDatasetSubmission($this->mockDatasetSubmissionCompleteMissingContact);
        $this->assertNull($this->dataset->getPrimaryPointOfContact());
    }
}
