<?php

namespace App\Entity;

use App\Enum\DatasetLifecycleStatus;
use App\Enum\KeywordType;
use App\Util\DatasetCitationUtil;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * Dataset Entity class.
 */
#[ORM\Entity(repositoryClass: 'App\Repository\DatasetRepository')]
class Dataset extends Entity
{
    /**
     * A friendly name for this type of entity.
     */
    public const FRIENDLY_NAME = 'Dataset';

    /**
     * A value for $datasetStatus that indicates no status has been set.
     */
    public const DATASET_STATUS_NONE = 'None';

    /**
     * A value for $datasetStatus that indicates that metadata has been submitted.
     */
    public const DATASET_STATUS_SUBMITTED = 'Submitted';

    /**
     * A value for $datasetStatus that indicates that the metadata is in review.
     */
    public const DATASET_STATUS_IN_REVIEW = 'InReview';

    /**
     * A value for $datasetStatus that indicates that the metadata has been accepted.
     */
    public const DATASET_STATUS_ACCEPTED = 'Accepted';

    /**
     * A value for $datasetStatus that indicates that the metadata has been sent back to the submitter for revision.
     */
    public const DATASET_STATUS_BACK_TO_SUBMITTER = 'BackToSubmitter';

    /**
     * Valid values for $datasetStatus.
     */
    public const DATASET_STATUSES = [
        self::DATASET_STATUS_NONE => 'No Status',
        self::DATASET_STATUS_SUBMITTED => 'Submitted',
        self::DATASET_STATUS_IN_REVIEW => 'In Review',
        self::DATASET_STATUS_ACCEPTED => 'Accepted',
        self::DATASET_STATUS_BACK_TO_SUBMITTER => 'Request Revisions',
    ];

    /**
     * Cold Storage Tag.
     */
    public const TAG_COLD_STORAGE = 'Cold Storage';

    /**
     * Remotely Hosted Tag.
     */
    public const TAG_REMOTELY_HOSTED = 'Remotely Hosted';

    /**
     * ERDDAP Tag.
     */
    public const TAG_ERDDAP = 'ERDDAP';

    /**
     * NCEI Tag.
     */
    public const TAG_NCEI = 'NCEI';

    /**
     * Valid Tags for a dataset.
     */
    public const TAGS = [
        self::TAG_COLD_STORAGE => 'Cold Storage',
        self::TAG_REMOTELY_HOSTED => 'Remotely Hosted',
        self::TAG_ERDDAP => 'ERDDAP',
        self::TAG_NCEI => 'NCEI',
    ];

    /**
     * The UDI for this Dataset.
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    #[Serializer\Groups(['card', 'search'])]
    #[Groups(['grp-dk-report', 'remotely-hosted-dataset-report'])]
    #[SerializedName('UDI')]
    protected $udi;

    /**
     * The title for this Dataset.
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    #[Serializer\Groups(['card', 'search'])]
    #[Groups(['grp-dk-report'])]
    protected $title;

    /**
     * The abstract for this Dataset.
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $abstract;

    /**
     * The DOI for this Dataset.
     *
     * @var DOI
     */
    #[ORM\OneToOne(targetEntity: 'DOI', cascade: ['persist'])]
    #[Serializer\Groups(['card'])]
    protected $doi;

    /**
     * The Research Group this Dataset is attached to.
     *
     * @var ResearchGroup
     *
     *
     */
    #[ORM\ManyToOne(targetEntity: 'ResearchGroup', inversedBy: 'datasets')]
    #[Serializer\MaxDepth(1)]
    #[Serializer\Groups(['search'])]
    protected $researchGroup;

    /**
     * The DIF for this Dataset.
     *
     * @var DIF
     */
    #[ORM\OneToOne(targetEntity: 'DIF', inversedBy: 'dataset')]
    #[Serializer\Groups(['card'])]
    protected $dif;

    /**
     * The most recent Dataset Submission for this dataset.
     *
     * @var DatasetSubmission
     */
    #[ORM\OneToOne(targetEntity: 'DatasetSubmission', cascade: ['remove'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    #[Serializer\Groups(['card'])]
    #[Groups(['remotely-hosted-dataset-report'])]
    protected $datasetSubmission;

    /**
     * All Dataset Submissions for this dataset.
     *
     * @var Collection
     */
    #[ORM\OneToMany(targetEntity: 'DatasetSubmission', mappedBy: 'dataset', cascade: ['remove'])]
    #[ORM\OrderBy(['sequence' => 'DESC'])]
    #[Serializer\Exclude]
    protected $datasetSubmissionHistory;

    /**
     * Accepted Date Timestamp for Dataset.
     *
     * @var \DateTime;
     */
    #[ORM\Column(type: 'datetimetz', nullable: true)]
    #[Serializer\Groups(['card'])]
    protected $acceptedDate;

    /**
     * The identified status of this Dataset.
     *
     * @var int
     *
     * @see DIF::STATUS_* constants.
     */
    #[ORM\Column(type: 'smallint')]
    protected $identifiedStatus = DIF::STATUS_UNSUBMITTED;

    /**
     * The latest saved dataset submission status.
     *
     * @var int
     *
     * @see DatasetSubmission::STATUS_* constants.
     */
    #[ORM\Column(type: 'smallint')]
    protected $datasetSubmissionStatus = DatasetSubmission::STATUS_UNSUBMITTED;

    /**
     * The status of this Dataset.
     *
     * @var string
     *
     * @see self::DATASET_STATUS_* constants.
     */
    #[ORM\Column(type: 'text')]
    protected $datasetStatus = self::DATASET_STATUS_NONE;

    /**
     * The availability status of this Dataset.
     *
     * @var int
     *
     * @see DatasetSubmission::AVAILABILITY_STATUS_* constants.
     */
    #[ORM\Column(type: 'smallint')]
    #[Serializer\Groups(['card'])]
    protected $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_NOT_AVAILABLE;

    /**
     * Collection of DatasetPublication.
     *
     * @var Collection
     */
    #[ORM\OneToMany(targetEntity: 'DatasetPublication', mappedBy: 'dataset', orphanRemoval: true)]
    #[Serializer\Groups(['publications'])]
    protected $datasetPublications;

    /**
     * The identifier for an issue tracking ticket related to this Dataset.
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $issueTrackingTicket;

    /**
     * Funders associated with this Dataset.
     *
     * @var Collection
     */
    #[ORM\ManyToMany(targetEntity: Funder::class)]
    protected $funders;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->datasetSubmissionHistory = new ArrayCollection();
        $this->funders = new ArrayCollection();
    }

    /**
     * Sets the UDI for this Dataset.
     *
     * @param string|null $udi the UDI for this Dataset
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
     * @return string the UDI for this Dataset
     */
    public function getUdi()
    {
        return $this->udi;
    }

    /**
     * Sets the Research Group this Dataset is attached to.
     *
     * @param ResearchGroup|null $researchGroup the Research Group this Dataset is attached to
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
     * @return ResearchGroup the Research Group this DIF is attached to
     */
    public function getResearchGroup()
    {
        return $this->researchGroup;
    }

    /**
     * Sets the DIF for this Dataset.
     *
     * @param DIF $dif the DIF for this Dataset
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
     * @return DIF the DIF for this Dataset
     */
    public function getDif()
    {
        return $this->dif;
    }

    /**
     * Set the latest Dataset Submission.
     *
     * @param DatasetSubmission $datasetSubmission the latest Dataset Submission
     *
     * @return void
     */
    public function setDatasetSubmission(DatasetSubmission $datasetSubmission)
    {
        if (
            DatasetSubmission::STATUS_COMPLETE === $datasetSubmission->getStatus()
            or DatasetSubmission::STATUS_IN_REVIEW === $datasetSubmission->getStatus()
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
    public function getDatasetSubmission(): ?DatasetSubmission
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
     * @param DOI $doi the DOI entity for this Dataset
     *
     * @return void
     */
    public function setDoi(DOI $doi)
    {
        $this->doi = $doi;
    }

    /**
     * Get the DOI for this Dataset.
     */
    public function getDoi(): ?DOI
    {
        return $this->doi;
    }

    /**
     * Get the DOI string for this Dataset.
     */
    #[Groups(['grp-dk-report'])]
    #[SerializedName('doi')]
    public function getDoiString(): string
    {
        return $this->getDoi()?->getDoi() ?? '';
    }

    /**
     * Set the identified status.
     *
     * @param int $identifiedStatus the identified status
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
     * @return int
     */
    public function getIdentifiedStatus()
    {
        return $this->identifiedStatus;
    }

    /**
     * Set the dataset submission status.
     *
     * @param int $datasetSubmissionStatus the dataset submission status
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
     * @return int
     */
    public function getDatasetSubmissionStatus()
    {
        return $this->datasetSubmissionStatus;
    }

    /**
     * Set the dataset status.
     *
     * @param string $datasetStatus the dataset status
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
     * @param int $availabilityStatus the availability status
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
     * @return int
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
     * @param string|null $issueTrackingTicket the identifier for an issue tracking ticket related to this Dataset
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
     * Add a Funder to Dataset's collection of Funders.
     */
    public function addFunder(Funder $funder): self
    {
        if (false === $this->funders->contains($funder)) {
            $this->funders->add($funder);
        }

        return $this;
    }

    /**
     * Remove a Funder from the Dataset's collection of Funders.
     */
    public function removeFunder(Funder $funder): self
    {
        $this->funders->removeElement($funder);

        return $this;
    }

    /**
     * Returns a collection of all Funders associated with this Dataset.
     *
     * @return Collection<int, Funder>
     */
    public function getFunders(): Collection
    {
        return $this->funders;
    }

    /**
     * Whether this Dataset has a DIF.
     *
     * @return bool
     */
    public function hasDif()
    {
        return $this->dif instanceof DIF;
    }

    /**
     * Whether this Dataset has a Dataset Submission.
     *
     * @return bool
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
            if (self::DATASET_STATUS_ACCEPTED === $this->getDatasetStatus()) {
                switch ($this->getDatasetSubmission()->getRestrictions()) {
                    case DatasetSubmission::RESTRICTION_NONE:
                        $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED;
                        break;
                    case DatasetSubmission::RESTRICTION_RESTRICTED:
                        $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED;
                        break;
                }
            } elseif (
                DatasetSubmission::STATUS_COMPLETE === $this->getDatasetSubmission()->getStatus()
                or DatasetSubmission::STATUS_IN_REVIEW === $this->getDatasetSubmission()->getStatus()
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
                    if (self::DATASET_STATUS_ACCEPTED === $this->getDatasetStatus()) {
                        switch ($this->getDatasetSubmission()->getRestrictions()) {
                            case DatasetSubmission::RESTRICTION_NONE:
                                $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE;
                                break;
                            case DatasetSubmission::RESTRICTION_RESTRICTED:
                                $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED;
                                break;
                        }
                    } elseif (
                        DatasetSubmission::STATUS_COMPLETE === $this->getDatasetSubmission()->getStatus()
                        or DatasetSubmission::STATUS_IN_REVIEW === $this->getDatasetSubmission()->getStatus()
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
     */
    public function isAvailable(): bool
    {
        return in_array(
            $this->availabilityStatus,
            [
                DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE,
                DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED,
            ]
        );
    }

    /**
     * Check if dataset is Identified.
     */
    public function isIdentified(): bool
    {
        return $this->getDatasetLifecycleStatus() == DatasetLifecycleStatus::IDENTIFIED;
    }

    /**
     * Check if dataset is Submitted.
     */
    public function isSubmitted(): bool
    {
        return $this->getDatasetLifecycleStatus() == DatasetLifecycleStatus::SUBMITTED;
    }

    /**
     * Check if Dataset is in Cold Storage.
     */
    public function isColdStored(): bool
    {
        if ($this->getDatasetSubmission() instanceof DatasetSubmission) {
            return $this->getDatasetSubmission()->isDatasetFileInColdStorage();
        }

        return false;
    }

    /**
     * Check if Dataset has ERDDAP URL.
     */
    public function hasErddapUrl(): bool
    {
        return $this->getDatasetSubmission()?->getErddapUrl() !== null;
    }

    /**
     * Check if Dataset has NCEI URL.
     */
    public function hasNceiUrl(): bool
    {
        return $this->getDatasetSubmission()?->getNceiUrl() !== null;
    }

    /**
     * Whether this Dataset is remotely hosted.
     */
    public function isRemotelyHosted(): bool
    {
        return in_array(
            $this->availabilityStatus,
            [
                DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED,
                DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED,
            ]
        );
    }

    /**
     * Whether this Dataset is restricted.
     */
    public function isRestricted(): bool
    {
        $isRestricted = false;
        if (
            $this->getDatasetSubmission() instanceof DatasetSubmission
            and DatasetSubmission::RESTRICTION_RESTRICTED === $this->getDatasetSubmission()->getRestrictions()
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
        if (DIF::STATUS_APPROVED == $difStatus) {
            $statusResult = 'DIF';
            if (self::DATASET_STATUS_IN_REVIEW == $datasetStatus) {
                $statusResult = 'In Review';
            } elseif (self::DATASET_STATUS_BACK_TO_SUBMITTER == $datasetStatus) {
                $statusResult = 'Back to Submitter';
            } elseif (self::DATASET_STATUS_ACCEPTED == $datasetStatus) {
                if (
                    DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED == $availabilityStatus
                    || DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED == $availabilityStatus
                ) {
                    $statusResult = 'Completed, Restricted';
                } elseif (
                    DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE == $availabilityStatus
                    || DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED == $availabilityStatus
                ) {
                    $statusResult = 'Completed';
                } else {
                    $statusResult = 'DIF';
                }
            } elseif (self::DATASET_STATUS_SUBMITTED == $datasetStatus) {
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
     * @return Person|null
     *
     * @throws \Exception if a Dataset Submission is encountered missing a contact
     */
    public function getPrimaryPointOfContact()
    {
        $datasetSubmission = $this->getDatasetSubmission();
        $dif = $this->getDif();

        // If we have a submission, use its POC.
        if (
            $datasetSubmission instanceof DatasetSubmission
            and DatasetSubmission::STATUS_COMPLETE == $datasetSubmission->getStatus()
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
     * @return string|null the gml for the dataset
     */
    public function getSpatialExtentGeometry()
    {
        $datasetSubmission = $this->getDatasetSubmission();
        $dif = $this->getDif();
        $spatialExtent = null;

        if ($datasetSubmission instanceof DatasetSubmission) {
            $spatialExtent = $datasetSubmission->getSpatialExtent();
        } elseif ($dif instanceof DIF and DIF::STATUS_APPROVED === $dif->getStatus() and $dif->getSpatialExtentGeometry()) {
            $spatialExtent = $dif->getSpatialExtentGeometry();
        }

        return $spatialExtent;
    }

    /**
     * Does this Dataset have a spatial extent?
     */
    #[Groups(['grp-dk-report'])]
    #[SerializedName('hasSpatialExtent')]
    public function hasSpatialExtent(): string
    {
        return null !== $this->getSpatialExtentGeometry() ? 'Yes' : 'No';
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
     * @param \DateTime $acceptedDate the accepted date for the Dataset
     *
     * @return void
     */
    public function setAcceptedDate(\DateTime $acceptedDate)
    {
        $this->acceptedDate = $acceptedDate;
    }

    /**
     * Getter for the latest dataset submission review copy.
     */
    public function getLatestDatasetReview(): ?DatasetSubmission
    {
        $datasetSubmission = ($this->getDatasetSubmissionHistory()->first() ? $this->getDatasetSubmissionHistory()->first() : null);

        if (
            self::DATASET_STATUS_IN_REVIEW === $this->getDatasetStatus()
            and $this->getDatasetStatus() !== $datasetSubmission->getDatasetStatus()
            and self::DATASET_STATUS_BACK_TO_SUBMITTER === $datasetSubmission->getDatasetStatus()
        ) {
            $datasetSubmission = $this->getDatasetSubmission();
        }

        return $datasetSubmission;
    }

    /**
     * Gets Project Directors associated with this Dataset.
     */
    public function getProjectDirectors(): ArrayCollection
    {
        $collection = new ArrayCollection();
        foreach ($this->getResearchGroup()->getPersonResearchGroups() as $personResearchGroup) {
            if (
                $personResearchGroup instanceof PersonResearchGroup
                and ResearchGroupRole::LEADERSHIP === $personResearchGroup->getRole()->getName()
            ) {
                $collection->add($personResearchGroup->getPerson());
            }
        }

        return $collection;
    }

    /**
     * Return the total file size for this dataset.
     *
     *
     */
    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('totalFileSize')]
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
     *
     */
    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('numberOfFiles')]
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
     * Show friendly name of this entity.
     *
     *
     *
     */
    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('friendlyName')]
    #[Serializer\Groups(['search'])]
    public function getFriendlyName(): string
    {
        return $this::FRIENDLY_NAME;
    }

    /**
     * Show class name of this entity.
     *
     *
     *
     */
    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('className')]
    #[Serializer\Groups(['search'])]
    public function getClassName(): string
    {
        return get_class($this);
    }

    /**
     * Returns the "tags" for this datasets.
     */
    public function getTags(): array
    {
        $tags = [];

        $datasetSubmission = $this->getDatasetSubmission();

        if ($datasetSubmission instanceof DatasetSubmission) {
            if ($datasetSubmission->isDatasetFileInColdStorage()) {
                $tags[] = self::TAG_COLD_STORAGE;
            }

            if (!empty($datasetSubmission->getNceiUrl())) {
                $tags[] = self::TAG_NCEI;
            }

            if (!empty($datasetSubmission->getErddapUrl())) {
                $tags[] = self::TAG_ERDDAP;
            }

            if ($datasetSubmission->isRemotelyHosted()) {
                $tags[] = self::TAG_REMOTELY_HOSTED;
            }
        }

        return $tags;
    }

    /**
     * Gets the UDI, stringifier.
     */
    public function __toString(): string
    {
        return $this->getUdi();
    }

    /**
     * Get the Dataset's Lifecycle Status.
     */
    #[Groups(['remotely-hosted-dataset-report'])]
    public function getDatasetLifecycleStatus(): DatasetLifecycleStatus
    {
        $datasetLifeCycleStatus = DatasetLifecycleStatus::NONE;

        if ((Dataset::DATASET_STATUS_ACCEPTED === $this->getDatasetStatus()) and (true === $this->isRestricted())) {
            $datasetLifeCycleStatus = DatasetLifecycleStatus::RESTRICTED;
        } elseif (Dataset::DATASET_STATUS_ACCEPTED === $this->getDatasetStatus()) {
            $datasetLifeCycleStatus = DatasetLifecycleStatus::AVAILABLE;
        } elseif ($this->hasDatasetSubmission()) {
            $datasetLifeCycleStatus = DatasetLifecycleStatus::SUBMITTED;
        } elseif ($this->hasDif() and DIF::STATUS_APPROVED == $this->getDif()->getStatus()) {
            $datasetLifeCycleStatus = DatasetLifecycleStatus::IDENTIFIED;
        }

        return $datasetLifeCycleStatus;
    }

    /**
     * Remove the DatasetSubmission reference.
     */
    #[ORM\PreRemove]
    public function removeDatasetSubmissionReference(): void
    {
        $this->datasetSubmission->setDataset(null);
    }

    /**
     * Get Dataset Lifecycle Status as a string.
     */
    #[Groups(['grp-dk-report'])]
    #[SerializedName('datasetLifecycleStatus')]
    public function getDatasetLifecycleStatusString(): string
    {
        return $this->getDatasetLifecycleStatus()->value;
    }

    /**
     * Get a link to the dataset landing page.
     */
    #[Groups(['grp-dk-report'])]
    public function getLandingPage(): string
    {
        return 'https://grp.griidc.org/data/' . $this->getUdi();
    }

    /**
     * Get ANZRC keywords as a string.
     */
    #[Groups(['grp-dk-report'])]
    public function getAnzsrcKeywords(): ?string
    {
        $keywords = $this->getKeywordsByType(KeywordType::TYPE_ANZSRC);
        return implode('|', $this->makeUniqueKeywordDictionary($keywords));
    }

    /**
     * Get GCMD keywords as a string.
     */
    #[Groups(['grp-dk-report'])]
    public function getGcmdKeywords(): ?string
    {
        $keywords = $this->getKeywordsByType(KeywordType::TYPE_GCMD);
        return implode('|', array_diff($this->makeUniqueKeywordDictionary($keywords), ['Science Keywords', 'Earth Science']));
    }

    /**
     * Make a unique dictionary of keywords.
     */
    private function makeUniqueKeywordDictionary(?Collection $keywords): array
    {
        $dictionary = [];

        if ($keywords instanceof Collection) {
            foreach ($keywords as $keyword) {
                $keywordParts = explode('>', $keyword->getDisplayPath());
                foreach ($keywordParts as $part) {
                    $part = trim($part);
                    $part = strtolower($part);
                    $dictionary[] = ucwords($part);
                }
            }
        }

        sort($dictionary);

        return array_unique($dictionary);
    }

    /**
     * Get theme keywords as string.
     */
    #[Groups(['grp-dk-report'])]
    public function getThemeKeywords(): ?string
    {
        $keywords = $this->getDatasetSubmission()?->getThemeKeywords();

        return implode('|', $keywords ?? []);
    }

    /**
     * Get place keywords as string.
     */
    #[Groups(['grp-dk-report'])]
    public function getPlaceKeywords(): ?string
    {
        $keywords = $this->getDatasetSubmission()?->getPlaceKeywords();

        return implode('|', $keywords ?? []);
    }

    /**
     * Get topic keywords as string.
     */
    #[Groups(['grp-dk-report'])]
    public function getTopicKeywords(): ?string
    {
        $keywords = $this->getDatasetSubmission()?->getTopicKeywords();

        return implode('|', $keywords ?? []);
    }

    /**
     * Get keywords by type.
     */
    private function getKeywordsByType(KeywordType $type): ?Collection
    {
        $keywords = $this->getDatasetSubmission()?->getKeywords();

        return $keywords = $keywords?->filter(function (Keyword $keyword) use ($type) {
                return $keyword->getType() === $type;
        });
    }

    /**
     * Get the dataset's Research Group name.
     */
    #[Groups(['grp-dk-report'])]
    public function getResearchGroupName(): string
    {
        return $this->getResearchGroup()->getName();
    }

    /**
     * Get the dataset's Funding Cycle name.
     */
    #[Groups(['grp-dk-report'])]
    public function getFundingCycleName(): string
    {
        return $this->getResearchGroup()->getFundingCycle()->getName();
    }
}
