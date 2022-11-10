<?php

namespace App\Entity;

use App\Util\DatasetCitationUtil;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Dataset Entity class.
 *
 * @ORM\Entity(repositoryClass="App\Repository\DatasetRepository")
 */
class Dataset extends Entity
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Dataset';

        /**
     * A value for $datasetStatus that indicates no status has been set.
     */
    const DATASET_STATUS_NONE = 'None';

    /**
     * A value for $datasetStatus that indicates that metadata has been submitted.
     */
    const DATASET_STATUS_SUBMITTED = 'Submitted';

    /**
     * A value for $datasetStatus that indicates that the metadata is in review.
     */
    const DATASET_STATUS_IN_REVIEW = 'InReview';

    /**
     * A value for $datasetStatus that indicates that the metadata has been accepted.
     */
    const DATASET_STATUS_ACCEPTED = 'Accepted';

    /**
     * A value for $datasetStatus that indicates that the metadata has been sent back to the submitter for revision.
     */
    const DATASET_STATUS_BACK_TO_SUBMITTER = 'BackToSubmitter';

    /**
     * Valid values for $datasetStatus.
     */
    const DATASET_STATUSES = array(
        self::DATASET_STATUS_NONE => 'No Status',
        self::DATASET_STATUS_SUBMITTED => 'Submitted',
        self::DATASET_STATUS_IN_REVIEW => 'In Review',
        self::DATASET_STATUS_ACCEPTED => 'Accepted',
        self::DATASET_STATUS_BACK_TO_SUBMITTER => 'Request Revisions',
    );

    /**
     * The UDI for this Dataset.
     *
     * @var string
     *
     * @Serializer\Groups({"card", "search"})
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $udi;

    /**
     * The title for this Dataset.
     *
     * @var string
     *
     * @Serializer\Groups({"card"})
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Serializer\Groups({"search"})
     */
    protected $title;

    /**
     * The abstract for this Dataset.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $abstract;

    /**
     * The DOI for this Dataset.
     *
     * @var DOI
     *
     * @Serializer\Groups({"card"})
     *
     * @ORM\OneToOne(targetEntity="DOI", cascade={"persist"})
     */
    protected $doi;

    /**
     * The Research Group this Dataset is attached to.
     *
     * @var ResearchGroup
     *
     * @Serializer\MaxDepth(1)
     * @Serializer\Groups({"search"})
     *
     * @ORM\ManyToOne(targetEntity="ResearchGroup", inversedBy="datasets")
     */
    protected $researchGroup;

    /**
     * The DIF for this Dataset.
     *
     * @var DIF
     *
     * @Serializer\Groups({"card"})
     *
     * @ORM\OneToOne(targetEntity="DIF", inversedBy="dataset")
     */
    protected $dif;

    /**
     * The most recent Dataset Submission for this dataset.
     *
     * @var DatasetSubmission
     *
     * @Serializer\Groups({"card"})
     *
     * @ORM\OneToOne(targetEntity="DatasetSubmission")
     */
    protected $datasetSubmission;

    /**
     * All Dataset Submissions for this dataset.
     *
     * @var Collection
     *
     * @Serializer\Exclude
     *
     * @ORM\OneToMany(targetEntity="DatasetSubmission", mappedBy="dataset", cascade={"remove"})
     *
     * @ORM\OrderBy({"sequence" = "DESC"})
     */
    protected $datasetSubmissionHistory;

    /**
     * Accepted Date Timestamp for Dataset.
     *
     * @var \DateTime $acceptedDate;
     *
     * @Serializer\Groups({"card"})
     *
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    protected $acceptedDate;

    /**
     * The identified status of this Dataset.
     *
     * @var integer
     *
     * @see DIF::STATUS_* constants.
     *
     * @ORM\Column(type="smallint")
     */
    protected $identifiedStatus = DIF::STATUS_UNSUBMITTED;

    /**
     * The latest saved dataset submission status.
     *
     * @var integer
     *
     * @see DatasetSubmission::STATUS_* constants.
     *
     * @ORM\Column(type="smallint")
     */
    protected $datasetSubmissionStatus = DatasetSubmission::STATUS_UNSUBMITTED;

    /**
     * The status of this Dataset.
     *
     * @var status
     *
     * @see self::DATASET_STATUS_* constants.
     *
     * @ORM\Column(type="text")
     */
    protected $datasetStatus = self::DATASET_STATUS_NONE;

    /**
     * The availability status of this Dataset.
     *
     * @var integer
     *
     * @see DatasetSubmission::AVAILABILITY_STATUS_* constants.
     *
     * @Serializer\Groups({"card"})
     *
     * @ORM\Column(type="smallint")
     */
    protected $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_NOT_AVAILABLE;

    /**
     * Collection of DatasetPublication.
     *
     * @var Collection
     *
     * @Serializer\Groups({"publications"})
     *
     * @ORM\OneToMany(targetEntity="DatasetPublication", mappedBy="dataset", orphanRemoval=true)
     */
    protected $datasetPublications;

    /**
     * The identifier for an issue tracking ticket related to this Dataset.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $issueTrackingTicket;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->datasetSubmissionHistory = new ArrayCollection();
    }

    /**
     * Sets the UDI for this Dataset.
     *
     * @param string|null $udi The UDI for this Dataset.
     *
     * @return void
     */
    public function setUdi(?string $udi)
    {
        $this->udi = $udi;
    }

    /**
     * Gets the UDI for this Dataset.
     *
     * @return string The UDI for this Dataset.
     */
    public function getUdi()
    {
        return $this->udi;
    }

    /**
     * Sets the Research Group this Dataset is attached to.
     *
     * @param ResearchGroup|null $researchGroup The Research Group this Dataset is attached to.
     *
     * @return void
     */
    public function setResearchGroup(ResearchGroup $researchGroup = null)
    {
        $this->researchGroup = $researchGroup;
    }

    /**
     * Gets the Research Group this Dataset is attached to.
     *
     * @return ResearchGroup The Research Group this DIF is attached to.
     */
    public function getResearchGroup()
    {
        return $this->researchGroup;
    }

    /**
     * Sets the DIF for this Dataset.
     *
     * @param DIF $dif The DIF for this Dataset.
     *
     * @return void
     */
    public function setDif(DIF $dif)
    {
        $this->dif = $dif;
        if ($this->dif->getDataset() !== $this) {
            $this->dif->setDataset($this);
        }
    }

    /**
     * Gets the DIF for this Dataset.
     *
     * @return DIF The DIF for this Dataset.
     */
    public function getDif()
    {
        return $this->dif;
    }

    /**
     * Set the latest Dataset Submission.
     *
     * @param DatasetSubmission $datasetSubmission The latest Dataset Submission.
     *
     * @return void
     */
    public function setDatasetSubmission(DatasetSubmission $datasetSubmission)
    {
        if (
            $datasetSubmission->getStatus() === DatasetSubmission::STATUS_COMPLETE or
            $datasetSubmission->getStatus() === DatasetSubmission::STATUS_IN_REVIEW
        ) {
            $this->datasetSubmission = $datasetSubmission;
        } else {
            $this->datasetSubmission = null;
        }
        $datasetSubmission->setDataset($this);
        $this->setDatasetSubmissionStatus($datasetSubmission->getStatus());
        $this->setDatasetStatus($datasetSubmission->getDatasetStatus());
        $this->updateAvailabilityStatus();
    }

    /**
     * Get the latest Dataset Submission.
     *
     * @return DatasetSubmission
     */
    public function getDatasetSubmission()
    {
        return $this->datasetSubmission;
    }

    /**
     * Get the Dataset Submission history.
     *
     * @return Collection
     */
    public function getDatasetSubmissionHistory()
    {
        return $this->datasetSubmissionHistory;
    }

    /**
     * Update the title for this Dataset.
     *
     * @return void
     */
    public function updateTitle()
    {
        // After Dataset is accepted, the getDatasetSubmission pointer points to the accepted version.
        if ($this->hasDatasetSubmission()) {
            // Copy DatasetSubmission title to Dataset.
            $this->title = $this->getDatasetSubmission()->getTitle();
        } elseif ($this->hasDif()) {
            // Copy DIF title to Dataset.
            $this->title = $this->getDif()->getTitle();
        }
    }

    /**
     * Get the title for this dataset.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Update the abstract for this Dataset.
     *
     * @return void
     */
    public function updateAbstract()
    {
        // After Dataset is accepted, the getDatasetSubmission pointer points to the accepted version.
        if ($this->hasDatasetSubmission()) {
            // Copy DatasetSubmission abstract to Dataset.
            $this->abstract = $this->getDatasetSubmission()->getAbstract();
        } elseif ($this->hasDif()) {
            // Copy DIF abstract to Dataset.
            $this->abstract = $this->getDif()->getAbstract();
        }
    }

    /**
     * Get the abstract for this dataset.
     *
     * @return string
     */
    public function getAbstract()
    {
        return $this->abstract;
    }

    /**
     * Get authors shortcut, to get Authors from DatasetSubmission.
     *
     * @return string|null
     */
    public function getAuthors()
    {
        if ($this->hasDatasetSubmission()) {
            return $this->getDatasetSubmission()->getAuthors();
        } else {
            return null;
        }
    }

    /**
     * Set the DOI for this Dataset.
     *
     * @param DOI $doi The DOI entity for this Dataset.
     *
     * @return void
     */
    public function setDoi(DOI $doi)
    {
        $this->doi = $doi;
    }

    /**
     * Get the DOI for this Dataset.
     *
     * @return DOI
     */
    public function getDoi()
    {
        return $this->doi;
    }

    /**
     * Set the identified status.
     *
     * @param integer $identifiedStatus The identified status.
     *
     * @return void
     */
    public function setIdentifiedStatus(int $identifiedStatus)
    {
        $this->identifiedStatus = $identifiedStatus;
    }

    /**
     * Get the identified status.
     *
     * @return integer
     */
    public function getIdentifiedStatus()
    {
        return $this->identifiedStatus;
    }

    /**
     * Set the dataset submission status.
     *
     * @param integer $datasetSubmissionStatus The dataset submission status.
     *
     * @return void
     */
    public function setDatasetSubmissionStatus(int $datasetSubmissionStatus)
    {
        $this->datasetSubmissionStatus = $datasetSubmissionStatus;
    }

    /**
     * Get the dataset submission status.
     *
     * @return integer
     */
    public function getDatasetSubmissionStatus()
    {
        return $this->datasetSubmissionStatus;
    }

    /**
     * Set the dataset status.
     *
     * @param string $datasetStatus The dataset status.
     *
     * @return void
     */
    public function setDatasetStatus(string $datasetStatus)
    {
        $this->datasetStatus = $datasetStatus;
    }

    /**
     * Get the dataset status.
     *
     * @return string
     */
    public function getDatasetStatus()
    {
        return $this->datasetStatus;
    }

    /**
     * Set the availability status.
     *
     * @param integer $availabilityStatus The availability status.
     *
     * @return void
     */
    public function setAvailabilityStatus(int $availabilityStatus)
    {
        $this->availabilityStatus = $availabilityStatus;
    }

    /**
     * Get the availability status.
     *
     * @return integer
     */
    public function getAvailabilityStatus()
    {
        return $this->availabilityStatus;
    }

    /**
     * Getter for DatasetPublications for this Dataset.
     *
     * @return DatasetPublication
     */
    public function getDatasetPublications()
    {
        return $this->datasetPublications;
    }

    /**
     * Gets Publications associated with this Dataset.
     *
     * @return ArrayCollection
     */
    public function getPublications()
    {
        $collection = new ArrayCollection();
        $datasetPublications = $this->getDatasetPublications();

        if (null != $datasetPublications) {
            foreach ($this->getDatasetPublications() as $datasetPublication) {
                $collection->add($datasetPublication->getPublication());
            }
        }
        return $collection;
    }

    /**
     * Sets the issue tracking ticket for this Dataset.
     *
     * @param string|null $issueTrackingTicket The identifier for an issue tracking ticket related to this Dataset.
     *
     * @return void
     */
    public function setIssueTrackingTicket(?string $issueTrackingTicket)
    {
        $this->issueTrackingTicket = $issueTrackingTicket;
    }

    /**
     * Gets the issue tracking ticket for this Dataset.
     *
     * @return string
     */
    public function getIssueTrackingTicket()
    {
        return $this->issueTrackingTicket;
    }

    /**
     * Get a citation for this Dataset.
     *
     * @return string
     */
    public function getCitation()
    {
        return DatasetCitationUtil::getCitation($this);
    }

    /**
     * Whether this Dataset has a DIF.
     *
     * @return boolean
     */
    public function hasDif()
    {
        return $this->dif instanceof DIF;
    }

    /**
     * Whether this Dataset has a Dataset Submission.
     *
     * @return boolean
     */
    public function hasDatasetSubmission()
    {
        return $this->datasetSubmission instanceof DatasetSubmission;
    }

    /**
     * Update the availability status based on current dataset submission.
     *
     * @return void
     */
    public function updateAvailabilityStatus()
    {
        if (!$this->getDatasetSubmission() instanceof DatasetSubmission) {
            // Set availability status to not available and return if we don't have a Dataset Submission.
            $this->setAvailabilityStatus(DatasetSubmission::AVAILABILITY_STATUS_NOT_AVAILABLE);
            return;
        }
        $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_NOT_AVAILABLE;

        // Updating availability status for datasets that are marked as REMOTELY HOSTED.
        if ($this->getDatasetSubmission()->isRemotelyHosted()) {
            if ($this->getDatasetStatus() === self::DATASET_STATUS_ACCEPTED) {
                switch ($this->getDatasetSubmission()->getRestrictions()) {
                    case DatasetSubmission::RESTRICTION_NONE:
                        $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED;
                        break;
                    case DatasetSubmission::RESTRICTION_RESTRICTED:
                        $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED;
                        break;
                }
            } elseif (
                $this->getDatasetSubmission()->getStatus() === DatasetSubmission::STATUS_COMPLETE or
                $this->getDatasetSubmission()->getStatus() === DatasetSubmission::STATUS_IN_REVIEW
            ) {
                $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_APPROVAL;
            } else {
                $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_SUBMISSION;
            }
        } else {
            // Updating availability status for datasets that are using FILE UPLOAD.
            $fileset = $this->getDatasetSubmission()->getFileset();
            if ($fileset instanceof Fileset) {
                if ($fileset->isDone()) {
                    if ($this->getDatasetStatus() === self::DATASET_STATUS_ACCEPTED) {
                        switch ($this->getDatasetSubmission()->getRestrictions()) {
                            case DatasetSubmission::RESTRICTION_NONE:
                                $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE;
                                break;
                            case DatasetSubmission::RESTRICTION_RESTRICTED:
                                $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED;
                                break;
                        }
                    } elseif (
                        $this->getDatasetSubmission()->getStatus() === DatasetSubmission::STATUS_COMPLETE or
                        $this->getDatasetSubmission()->getStatus() === DatasetSubmission::STATUS_IN_REVIEW
                    ) {
                        $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_APPROVAL;
                    } else {
                        $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_SUBMISSION;
                    }
                } else {
                    $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_APPROVAL;
                }
            } else {
                if ($this->getDatasetSubmission()->getRemotelyHostedUrl()) {
                    $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_APPROVAL;
                }
            }
        }
        $this->setAvailabilityStatus($availabilityStatus);
    }

    /**
     * Whether this Dataset is available.
     *
     * @return boolean
     */
    public function isAvailable(): bool
    {
        return in_array(
            $this->availabilityStatus,
            array(
                DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE,
                DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED
            )
        );
    }

    /**
     * Whether this Dataset is remotely hosted.
     *
     * @return boolean
     */
    public function isRemotelyHosted(): bool
    {
        return in_array(
            $this->availabilityStatus,
            array(
                DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED,
                DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED
            )
        );
    }

    /**
     * Whether this Dataset is restricted.
     *
     * @return boolean
     */
    public function isRestricted(): bool
    {
        $isRestricted = false;
        if (
            $this->getDatasetSubmission() instanceof DatasetSubmission and
            $this->getDatasetSubmission()->getRestrictions() === DatasetSubmission::RESTRICTION_RESTRICTED
        ) {
            $isRestricted = true;
        }
        return $isRestricted;
    }

    /**
     * Return a value that represents the status of the Dataset as understood in the work flow.
     *
     * @return string The value of the status like that tabulated in MDAPP
     */
    public function getStatus()
    {
        $difStatus = $this->getDif()->getStatus();
        $datasetStatus = $this->getDatasetStatus();
        $availabilityStatus = $this->getAvailabilityStatus();

        $statusResult = 'NoDif';
        if ($difStatus == DIF::STATUS_APPROVED) {
            $statusResult = 'DIF';
            if ($datasetStatus == self::DATASET_STATUS_IN_REVIEW) {
                $statusResult = 'In Review';
            } elseif ($datasetStatus == self::DATASET_STATUS_BACK_TO_SUBMITTER) {
                $statusResult = 'Back to Submitter';
            } elseif ($datasetStatus == self::DATASET_STATUS_ACCEPTED) {
                if (
                    $availabilityStatus == DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED ||
                    $availabilityStatus == DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED
                ) {
                    $statusResult = 'Completed, Restricted';
                } elseif (
                    $availabilityStatus == DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE ||
                    $availabilityStatus == DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED
                ) {
                    $statusResult = 'Completed';
                } else {
                    $statusResult = 'DIF';
                }
            } elseif ($datasetStatus == self::DATASET_STATUS_SUBMITTED) {
                $statusResult = 'Submitted';
            } else {
                //  $difStatus == DIF::STATUS_APPROVED
                $statusResult = 'DIF';
            }
        } else {
            $statusResult = 'NoDif';
        }
        return $statusResult;
    }

    /**
     * Gets the Dataset's Primary Point of Contact Person.
     *
     * @throws \Exception If a Dataset Submission is encountered missing a contact.
     *
     * @return Person|null
     */
    public function getPrimaryPointOfContact()
    {
        $datasetSubmission = $this->getDatasetSubmission();
        $dif = $this->getDif();

        // If we have a submission, use its POC.
        if (
            $datasetSubmission instanceof DatasetSubmission
            and $datasetSubmission->getStatus() == DatasetSubmission::STATUS_COMPLETE
        ) {
            $datasetContacts = $datasetSubmission->getDatasetContacts();
            if (count($datasetContacts) > 0) {
                return $datasetContacts->first()->getPerson();
            } else {
                return null;
            }
        // Otherwise, use the POC from an approved dif.
        } elseif ($dif instanceof DIF and DIF::STATUS_APPROVED == $dif->getStatus()) {
            return $dif->getPrimaryPointOfContact();
        } else {
            // And if we don't have an approved DIF, return nothing.
            return null;
        }
    }

    /**
     * Get the spatialExtentGeometry for this Metadata.
     *
     * @return string|null  The gml for the dataset.
     */
    public function getSpatialExtentGeometry()
    {
        $datasetSubmission = $this->getDatasetSubmission();
        $dif = $this->getDif();
        $spatialExtent = null;

        if ($datasetSubmission instanceof DatasetSubmission) {
            $spatialExtent = $datasetSubmission->getSpatialExtent();
        } elseif ($dif instanceof DIF and $dif->getStatus() === DIF::STATUS_APPROVED and $dif->getSpatialExtentGeometry()) {
            $spatialExtent = $dif->getSpatialExtentGeometry();
        }

        return $spatialExtent;
    }

    /**
     * Getter to accepted date.
     *
     * @return \DateTime
     */
    public function getAcceptedDate()
    {
        return $this->acceptedDate;
    }

    /**
     * Setter to accepted date.
     *
     * @param \DateTime $acceptedDate The accepted date for the Dataset.
     *
     * @return void
     */
    public function setAcceptedDate(\DateTime $acceptedDate)
    {
        $this->acceptedDate = $acceptedDate;
    }

    /**
     * Getter for the latest dataset submission review copy.
     *
     * @return DatasetSubmission
     */
    public function getLatestDatasetReview()
    {
        $datasetSubmission = ($this->getDatasetSubmissionHistory()->first() ? $this->getDatasetSubmissionHistory()->first() : null);

        if (
            $this->getDatasetStatus() === self::DATASET_STATUS_IN_REVIEW
            and $this->getDatasetStatus() !== $datasetSubmission->getDatasetStatus()
            and $datasetSubmission->getDatasetStatus() === self::DATASET_STATUS_BACK_TO_SUBMITTER
        ) {
            $datasetSubmission = $this->getDatasetSubmission();
        }

        return $datasetSubmission;
    }

    /**
     * Gets Project Directors associated with this Dataset.
     *
     * @return ArrayCollection
     */
    public function getProjectDirectors(): ArrayCollection
    {
        $collection = new ArrayCollection();
        foreach ($this->getResearchGroup()->getPersonResearchGroups() as $personResearchGroup) {
            if (
                $personResearchGroup instanceof PersonResearchGroup
                and $personResearchGroup->getRole()->getName() === ResearchGroupRole::LEADERSHIP
            ) {
                $collection->add($personResearchGroup->getPerson());
            }
        }

        return $collection;
    }

    /**
     * Return the total file size for this dataset.
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("totalFileSize")
     *
     * @return integer|null
     */
    public function getTotalFileSize(): ?int
    {
        $datasetSubmission = $this->getDatasetSubmission();
        if ($datasetSubmission instanceof DatasetSubmission) {
            $fileSet = $datasetSubmission->getFileset();
            if ($fileSet instanceof Fileset) {
                return $fileSet->getFileSize();
            }
        }

        return null;
    }

     /**
     * Return the number of files in this dataset.
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("numberOfFiles")
     *
     * @return integer|null
     */
    public function getNumberOfFiles(): ?int
    {
        $datasetSubmission = $this->getDatasetSubmission();
        if ($datasetSubmission instanceof DatasetSubmission) {
            $fileSet = $datasetSubmission->getFileset();
            if ($fileSet instanceof Fileset) {
                return $fileSet->getNumberOfFiles();
            }
        }

        return null;
    }

    /**
     * Returns the "tags" for this datasets.
     *
     * @return array
     */
    public function getTags(): array
    {
        $tags = [];

        $datasetSubmission = $this->getDatasetSubmission();

        if ($datasetSubmission instanceof DatasetSubmission) {
            if ($datasetSubmission->isDatasetFileInColdStorage()) {
                $tags[] = 'Coldstorage';
            }

            if (!empty($datasetSubmission->getNceiUrl())) {
                $tags[] = 'NCEI';
            }

            if (!empty($datasetSubmission->getErddapUrl())) {
                $tags[] = 'ERDAPP';
            }

            if ($datasetSubmission->isRemotelyHosted()) {
                $tags[] = 'Remotely Hosted';
            }
        }

        return $tags;
    }

    /**
     * Show friendly name of this entity.
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("friendlyName")
     * @Serializer\Groups({"search"})
     *
     * @return string
     */
    public function getFriendlyName(): string
    {
        return $this::FRIENDLY_NAME;
    }

    /**
     * Show class name of this entity.
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("className")
     * @Serializer\Groups({"search"})
     *
     * @return string
     */
    public function getClassName(): string
    {
        return get_class($this);
    }
}
