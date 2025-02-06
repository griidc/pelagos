<?php

namespace App\Tests\Entity;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\DIF;
use App\Entity\Fileset;
use App\Entity\Funder;
use App\Entity\Person;
use App\Entity\PersonDatasetSubmissionDatasetContact;
use App\Enum\DatasetLifecycleStatus;
use PHPUnit\Framework\TestCase;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Unit tests for  App\Entity\Dataset.
 */
class DatasetTest extends TestCase
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
     * Class variable containing a complete DatasetSubmission used for testing, restricted.
     *
     * @var mixed datasetSubmission
     */
    protected $mockDatasetSubmissionCompleteRestricted;

    /**
     * Class variable containing a complete, but in-review DatasetSubmission used for testing.
     *
     * @var mixed datasetSubmission
     */
    protected $mockDatasetSubmissionCompleteUnapproved;

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
     * Class variable type Dataset containing no DIF used for testing.
     *
     * @var mixed mockSubmittedNoDif
     */
    protected $mockDifStatusUnsubmitted;

    /**
     * Class variable holding WKT to test with.
     *
     * @var mixed auxMockSpatialExtentDif
     */
    protected $auxMockSpatialExtensionDif;

    /**
     * Class variable holding WKT to test with.
     *
     * @var mixed auxMockSpatialExtentDatasetSubmission
     */
    protected $auxMockSpatialExtensionDatasetSubmission;

    /**
     * Variable to hold Dataset Submission with missing contact.
     */
    protected $mockDatasetSubmissionCompleteMissingContact;

    /**
     * Variable for DIF that is not submitted.
     */
    protected $mockDifNStatusUnsubmitted;

    /**
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of Dataset and sets (some of) its properties.
     *
     * @return void
     */
    public function setUp()
    {
        $this->auxMockSpatialExtensionDif = 'LINESTRING (30 10, 10 30, 40 40)';

        $this->auxMockSpatialExtensionDatasetSubmission = 'MULTILINESTRING ((10 10, 20 20, 10 40),(40 40, 30 30, 40 20, 30 10))';

        $this->mockDatasetSubmissionComplete = \Mockery::mock(
            DatasetSubmission::class,
            array(
                'getStatus' => DatasetSubmission::STATUS_COMPLETE,
                'setDataset' => null,
                'getDatasetStatus' => Dataset::DATASET_STATUS_ACCEPTED,
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
                'getSpatialExtent' => $this->auxMockSpatialExtensionDatasetSubmission,
                'isRemotelyHosted' => false,
                'getFileset' => \Mockery::mock(
                    Fileset::class,
                    array(
                        'isDone' => true
                    )
                ),
            )
        );

        $this->mockDatasetSubmissionCompleteRestricted = \Mockery::mock(
            DatasetSubmission::class,
            array(
                'getStatus' => DatasetSubmission::STATUS_COMPLETE,
                'setDataset' => null,
                'getDatasetStatus' => Dataset::DATASET_STATUS_ACCEPTED,
                'getDatasetFileTransferStatus' => null,
                'getRestrictions' => DatasetSubmission::RESTRICTION_RESTRICTED,
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
                'getSpatialExtent' => $this->auxMockSpatialExtensionDatasetSubmission,
                'isRemotelyHosted' => false,
                'getFileset' => \Mockery::mock(
                    Fileset::class,
                    array(
                        'isDone' => true
                    )
                ),
            )
        );

        $this->mockDatasetSubmissionCompleteUnapproved = \Mockery::mock(
            DatasetSubmission::class,
            array(
                'getStatus' => DatasetSubmission::STATUS_COMPLETE,
                'setDataset' => null,
                'getDatasetStatus' => Dataset::DATASET_STATUS_IN_REVIEW,
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
                'getSpatialExtent' => $this->auxMockSpatialExtensionDatasetSubmission,
                'isRemotelyHosted' => false,
                'getFileset' => \Mockery::mock(
                    Fileset::class,
                    array(
                        'isDone' => true
                    )
                ),
            )
        );

        $this->mockDatasetSubmissionIncomplete = \Mockery::mock(
            DatasetSubmission::class,
            array(
                'getStatus' => DatasetSubmission::STATUS_INCOMPLETE,
                'setDataset' => null,
                'getDatasetStatus' => Dataset::DATASET_STATUS_NONE,
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
                'getSpatialExtent' => 'gibberish',
                'getFileset' => \Mockery::mock(
                    Fileset::class,
                    array(
                        'isDone' => true
                    )
                ),
            )
        );

        $this->mockDatasetSubmissionCompleteMissingContact = \Mockery::mock(
            DatasetSubmission::class,
            array(
                'getStatus' => DatasetSubmission::STATUS_COMPLETE,
                'setDataset' => null,
                'getDatasetStatus' => Dataset::DATASET_STATUS_ACCEPTED,
                'getDatasetFileTransferStatus' => null,
                'getRestrictions' => null,
                'getDatasetContacts' => new ArrayCollection(),
                'isRemotelyHosted' => false,
                'getFileset' => \Mockery::mock(
                    Fileset::class,
                    array(
                        'isDone' => true
                    )
                )
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
                'getSpatialExtentGeometry' => $this->auxMockSpatialExtensionDif,
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
                'getSpatialExtentGeometry' => 'gibberish',
            )
        );

        $this->mockDifNStatusUnsubmitted = \Mockery::mock(
            DIF::class,
            array(
                'getStatus' => DIF::STATUS_UNSUBMITTED,
                'setDataset' => null,
                'getDataset' => $this->dataset,
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
        $this->assertEquals(
            $this->auxMockSpatialExtensionDatasetSubmission,
            $this->dataset->getSpatialExtentGeometry()
        );

        // Case: We have an incomplete submission and an approved DIF.
        $this->dataset->setDif($this->mockApprovedDif);
        $this->dataset->setDatasetSubmission($this->mockDatasetSubmissionIncomplete);
        $this->assertEquals(
            'approved.dif@test.null',
            $this->dataset->getPrimaryPointOfContact()->getEmailAddress()
        );
        $this->assertEquals(
            $this->auxMockSpatialExtensionDif,
            $this->dataset->getDif()->getSpatialExtentGeometry()
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
     * Test the Dataset getDatasetLifecycleStatus method.
     */
    public function testGetDatasetLifecycleStatus()
    {
        // Case: Unapproved DIF
        $this->dataset->setDif($this->mockDifNStatusUnsubmitted);
        $this->assertEquals(
            DatasetLifecycleStatus::NONE,
            $this->dataset->getDatasetLifecycleStatus()
        );

        // Case: Dif is approved, no submission
        $this->dataset->setDif($this->mockApprovedDif);
        $this->assertEquals(
            DatasetLifecycleStatus::IDENTIFIED,
            $this->dataset->getDatasetLifecycleStatus()
        );

        // Case: draft submission (we don't consider it a submission at this point)
        $this->dataset->setDif($this->mockApprovedDif);
        $this->dataset->setDatasetSubmission($this->mockDatasetSubmissionIncomplete);
        $this->assertEquals(
            DatasetLifecycleStatus::IDENTIFIED,
            $this->dataset->getDatasetLifecycleStatus()
        );

        // Case: unapproved (but submitted) submission
        $this->dataset->setDif($this->mockApprovedDif);
        $this->dataset->setDatasetSubmission($this->mockDatasetSubmissionCompleteUnapproved);
        $this->assertEquals(
            DatasetLifecycleStatus::SUBMITTED,
            $this->dataset->getDatasetLifecycleStatus()
        );

        // Case: approved submission, non-restricted
        $this->dataset->setDif($this->mockApprovedDif);
        $this->dataset->setDatasetSubmission($this->mockDatasetSubmissionComplete);
        $this->assertEquals(
            DatasetLifecycleStatus::AVAILABLE,
            $this->dataset->getDatasetLifecycleStatus()
        );

        // Case: approved submission, restricted
        $this->dataset->setDif($this->mockApprovedDif);
        $this->dataset->setDatasetSubmission($this->mockDatasetSubmissionCompleteRestricted);
        $this->assertEquals(
            DatasetLifecycleStatus::RESTRICTED,
            $this->dataset->getDatasetLifecycleStatus()
        );
}

    /**
     * Test the Dataset getStatus() function.
     *
     * @return void
     */
    public function testGetStatus()
    {
        // Case: There is not a DIF set for this Dataset.

        $this->dataset->setDif($this->mockDifNStatusUnsubmitted);

        $this->assertEquals(
            'NoDif',
            $this->dataset->getStatus()
        );

        // Case: Dif is approved && dataset status is not one of the valid process status
        $this->dataset->setDif($this->mockApprovedDif);
        $this->dataset->setDatasetStatus(13);
        $this->assertEquals(
            'DIF',
            $this->dataset->getStatus()
        );

        // Case: Dif is approved && dataset statusis in review
        $this->dataset->setDif($this->mockApprovedDif);
        $this->dataset->setDatasetStatus(Dataset::DATASET_STATUS_IN_REVIEW);
        $this->assertEquals(
            'In Review',
            $this->dataset->getStatus()
        );

        // Case: Dif is approved && dataset status is back to submitter
        $this->dataset->setDif($this->mockApprovedDif);
        $this->dataset->setDatasetStatus(Dataset::DATASET_STATUS_BACK_TO_SUBMITTER);
        $this->assertEquals(
            'Back to Submitter',
            $this->dataset->getStatus()
        );

        // Case: Dif is approved && dataset status is accepted && availability status is restricted distribution
        $this->dataset->setDif($this->mockApprovedDif);
        $this->dataset->setDatasetStatus(Dataset::DATASET_STATUS_ACCEPTED);
        $this->dataset->setAvailabilityStatus(DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED);
        $this->assertEquals(
            'Completed, Restricted',
            $this->dataset->getStatus()
        );

        // Case: Dif is approved && dataset status is accepted && availability status is remotelyhosted distribution
        $this->dataset->setDif($this->mockApprovedDif);
        $this->dataset->setDatasetStatus(Dataset::DATASET_STATUS_ACCEPTED);
        $this->dataset->setAvailabilityStatus(DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED);
        $this->assertEquals(
            'Completed, Restricted',
            $this->dataset->getStatus()
        );

        // Case: Dif is approved && dataset status is accepted && availability status is publicly available
        $this->dataset->setDif($this->mockApprovedDif);
        $this->dataset->setDatasetStatus(Dataset::DATASET_STATUS_ACCEPTED);
        $this->dataset->setAvailabilityStatus(DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE);
        $this->assertEquals(
            'Completed',
            $this->dataset->getStatus()
        );

        // Case: Dif is approved &&
        // dataset status is accepted &&
        // availability status is publicly available remotely hosted
        $this->dataset->setDif($this->mockApprovedDif);
        $this->dataset->setDatasetStatus(Dataset::DATASET_STATUS_ACCEPTED);
        $this->dataset->setAvailabilityStatus(
            DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED
        );
        $this->assertEquals(
            'Completed',
            $this->dataset->getStatus()
        );


        // Case: Dif is approved &&
        // dataset status is accepted &&
        // availability status is something other than expected
        $this->dataset->setDif($this->mockApprovedDif);
        $this->dataset->setDatasetStatus(Dataset::DATASET_STATUS_ACCEPTED);
        $this->dataset->setAvailabilityStatus(0);
        $this->assertEquals(
            'DIF',
            $this->dataset->getStatus()
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

    /**
     * Test add/remove/get Funders methods.
     *
     * @return void
     */
    public function testAddRemoveFunder()
    {
        $funder = new Funder();
        $funder->setName('TestFunder');
        $this->dataset->addFunder($funder);
        $this->assertTrue($this->dataset->getFunders()->contains($funder));
        $this->dataset->removeFunder($funder);
        $this->assertFalse($this->dataset->getFunders()->contains($funder));
    }
}
