<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Dataset Entity class.
 *
 * @ORM\Entity(repositoryClass="Pelagos\Entity\DatasetRepository")
 */
class Dataset extends Entity
{

    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Dataset';

    /**
     * The UDI for this Dataset.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $udi;

    /**
     * The title for this Dataset.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
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
     * @ORM\OneToOne(targetEntity="DOI", cascade={"persist"})
     */
    protected $doi;

    /**
     * The Research Group this Dataset is attached to.
     *
     * @var ResearchGroup
     *
     * @ORM\ManyToOne(targetEntity="ResearchGroup", inversedBy="datasets")
     */
    protected $researchGroup;

    /**
     * The DIF for this Dataset.
     *
     * @var DIF
     *
     * @ORM\OneToOne(targetEntity="DIF", inversedBy="dataset")
     */
    protected $dif;

    /**
     * The most recent Dataset Submission for this dataset.
     *
     * @var DatasetSubmission
     *
     * @ORM\OneToOne(targetEntity="DatasetSubmission")
     */
    protected $datasetSubmission;

    /**
     * All Dataset Submissions for this dataset.
     *
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="DatasetSubmission", mappedBy="dataset", cascade={"remove"})
     *
     * @ORM\OrderBy({"sequence" = "DESC"})
     */
    protected $datasetSubmissionHistory;

    /**
     * The metadata for this dataset.
     *
     * @var Metadata
     *
     * @ORM\OneToOne(targetEntity="Metadata", mappedBy="dataset", cascade={"remove"})
     */
    protected $metadata;

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
     * The dataset submission status of this Dataset.
     *
     * @var integer
     *
     * @see DatasetSubmission::STATUS_* constants.
     *
     * @ORM\Column(type="smallint")
     */
    protected $datasetSubmissionStatus = DatasetSubmission::STATUS_UNSUBMITTED;

    /**
     * The metadata status of this Dataset.
     *
     * @var status
     *
     * @see DatasetSubmission::METADATA_STATUS_* constants.
     *
     * @ORM\Column(type="text")
     */
    protected $metadataStatus = DatasetSubmission::METADATA_STATUS_NONE;

    /**
     * The availability status of this Dataset.
     *
     * @var integer
     *
     * @see DatasetSubmission::AVAILABILITY_STATUS_* constants.
     *
     * @ORM\Column(type="smallint")
     */
    protected $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_NOT_AVAILABLE;

    /**
     * Collection of DatasetPublication.
     *
     * @var Collection
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
     * @param string $udi The UDI for this Dataset.
     *
     * @return void
     */
    public function setUdi($udi)
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
        if ($datasetSubmission->getStatus() === DatasetSubmission::STATUS_COMPLETE) {
            $this->datasetSubmission = $datasetSubmission;
        } else {
            $this->datasetSubmission = null;
        }
        $datasetSubmission->setDataset($this);
        $this->setDatasetSubmissionStatus($datasetSubmission->getStatus());
        $this->setMetadataStatus($datasetSubmission->getMetadataStatus());
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
     * Get the Metadata.
     *
     * @return Collection
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Set the Metadata.
     *
     * @param Metadata $metadata The metadata for this dataset.
     *
     * @return void
     */
    public function setMetadata(Metadata $metadata)
    {
        $this->metadata = $metadata;
        if ($this->metadata->getDataset() !== $this) {
            $this->metadata->setDataset($this);
        }
    }

    /**
     * Update the title for this Dataset.
     *
     * @return void
     */
    public function updateTitle()
    {
        if ($this->hasMetadata()) {
            // Copy Metadata title to Dataset.
            $this->title = $this->getMetadata()->getTitle();
        } elseif ($this->hasDatasetSubmission()) {
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
        if ($this->hasMetadata()) {
            // Copy Metadata abstract to Dataset.
            $this->abstract = $this->getMetadata()->getAbstract();
        } elseif ($this->hasDatasetSubmission()) {
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
     * Get reference date shortcut, to get Authors from DatasetSubmission.
     *
     * @return string|null
     */
    public function getReferenceDateYear()
    {
        if ($this->hasDatasetSubmission()) {
            return $this->getDatasetSubmission()->getReferenceDate()->format('Y');
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
    public function setIdentifiedStatus($identifiedStatus)
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
    public function setDatasetSubmissionStatus($datasetSubmissionStatus)
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
     * Set the metadata status.
     *
     * @param string $metadataStatus The metadata status.
     *
     * @return void
     */
    public function setMetadataStatus($metadataStatus)
    {
        $this->metadataStatus = $metadataStatus;
    }

    /**
     * Get the metadata status.
     *
     * @return string
     */
    public function getMetadataStatus()
    {
        return $this->metadataStatus;
    }

    /**
     * Set the availability status.
     *
     * @param integer $availabilityStatus The availability status.
     *
     * @return void
     */
    public function setAvailabilityStatus($availabilityStatus)
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
        $collection = new ArrayCollection;
        foreach ($this->getDatasetPublications() as $datasetPublication) {
            $collection->add($datasetPublication->getPublication());
        }
        return $collection;
    }

    /**
     * Sets the issue tracking ticket for this Dataset.
     *
     * @param string $issueTrackingTicket The identifier for an issue tracking ticket related to this Dataset.
     *
     * @return void
     */
    public function setIssueTrackingTicket($issueTrackingTicket)
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
        $datasetSubmission = $this->getDatasetSubmission();

        $title = $this->getTitle();
        $title = preg_replace('/\.$/', '', $title);
        $udi = $this->getUdi();

        if ($datasetSubmission instanceof DatasetSubmission) {
            $author = $datasetSubmission->getAuthors();
            $year = $datasetSubmission->getModificationTimeStamp()->format('Y');
            $doi = $datasetSubmission->getDoi();

            $citationString = $author . ' (' . $year . ') ' . $title . '.' .
                ' Distributed by: Gulf of Mexico Research Initiative Information and Data Cooperative '
                . '(GRIIDC), Harte Research Institute, Texas A&M University-Corpus Christi. ';

            if (null !== $doi) {
                $citationString .= "doi: $doi";
            } else {
                $citationString .= "Available from: http://data.gulfresearchinitiative.org/data/$udi";
            }
            return $citationString;
        } else {
            $citationString = "This dataset has no registration: $title ($udi)";
            return $citationString;
        }
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
     * Whether this Dataset has Metadata.
     *
     * @return boolean
     */
    public function hasMetadata()
    {
        return $this->metadata instanceof Metadata;
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
        switch ($this->getDatasetSubmission()->getDatasetFileTransferStatus()) {
            case DatasetSubmission::TRANSFER_STATUS_COMPLETED:
                if ($this->getDatasetSubmission()->getMetadataStatus() === DatasetSubmission::METADATA_STATUS_ACCEPTED) {
                    switch ($this->getDatasetSubmission()->getRestrictions()) {
                        case DatasetSubmission::RESTRICTION_NONE:
                            $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE;
                            break;
                        case DatasetSubmission::RESTRICTION_RESTRICTED:
                            $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED;
                            break;
                    }
                } elseif ($this->getDatasetSubmission()->getStatus() === DatasetSubmission::STATUS_COMPLETE) {
                    $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_APPROVAL;
                } else {
                    $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_SUBMISSION;
                }
                break;
            case DatasetSubmission::TRANSFER_STATUS_REMOTELY_HOSTED:
                if ($this->getDatasetSubmission()->getMetadataStatus() === DatasetSubmission::METADATA_STATUS_ACCEPTED) {
                    switch ($this->getDatasetSubmission()->getRestrictions()) {
                        case DatasetSubmission::RESTRICTION_NONE:
                            $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED;
                            break;
                        case DatasetSubmission::RESTRICTION_RESTRICTED:
                            $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED;
                            break;
                    }
                } elseif ($this->getDatasetSubmission()->getStatus() === DatasetSubmission::STATUS_COMPLETE) {
                    $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_APPROVAL;
                } else {
                    $availabilityStatus = DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_SUBMISSION;
                }
                break;
        }
        $this->setAvailabilityStatus($availabilityStatus);
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

        // If we have a complete submission, use its POC.
        if ($datasetSubmission instanceof DatasetSubmission
            and $datasetSubmission->getStatus() == DatasetSubmission::STATUS_COMPLETE) {

            $datasetContacts = $datasetSubmission->getDatasetContacts();
            if (count($datasetContacts) > 0) {
                return $datasetContacts->first()->getPerson();
            } else {
                throw new \Exception('A Dataset Submission was found that was missing a contact.');
            }
        // Otherwise, use the POC from an approved dif.
        } elseif ($dif instanceof DIF and DIF::STATUS_APPROVED == $dif->getStatus()) {
            return $dif->getPrimaryPointOfContact();
        } else {
            // And if we don't have an approved DIF, return nothing.
            return null;
        }

    }
}
