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
     * @ORM\Column(nullable=true)
     */
    protected $udi;

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
     * @ORM\OneToMany(targetEntity="DatasetSubmission", mappedBy="dataset")
     */
    protected $datasetSubmissionHistory;

    /**
     * The metadata for this dataset.
     *
     * @var Metadata
     *
     * @ORM\OneToOne(targetEntity="Metadata", mappedBy="dataset")
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
     * @ORM\Column
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
     * @ORM\OneToMany(targetEntity="DatasetPublication", mappedBy="dataset")
     */
    protected $datasetPublications;

    /**
     * The identifier for an issue tracking ticket related to this Dataset.
     *
     * @var string
     *
     * @ORM\Column(nullable=true)
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
        $this->datasetSubmission = $datasetSubmission;
        $this->datasetSubmission->setDataset($this);
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
     * Get the title for this dataset.
     *
     * @return string
     */
    public function getTitle()
    {
        // If this Dataset has a submission.
        if (null !== $this->datasetSubmission) {
            // Return its title.
            return $this->datasetSubmission->getTitle();
        }
        // If this Dataset only has a DIF.
        if (null !== $this->dif) {
            // Return its title.
            return $this->dif->getTitle();
        }
        // Return null if we can't find a title.
        return null;
    }

    /**
     * Get the abstract for this dataset.
     *
     * @return string
     */
    public function getAbstract()
    {
        // If this Dataset has a submission.
        if (null !== $this->datasetSubmission) {
            // Return its abstract.
            return $this->datasetSubmission->getAbstract();
        }
        // If this Dataset only has a DIF.
        if (null !== $this->dif) {
            // Return its abstract.
            return $this->dif->getAbstract();
        }
        // Return null if we can't find a abstract.
        return null;
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
        $udi = $this->getUdi();

        if ($datasetSubmission instanceof DatasetSubmission) {
            $author = $datasetSubmission->getAuthors();
            $year = $datasetSubmission->getModificationTimeStamp()->format('Y');
            $doi = $datasetSubmission->getDoi();

            if ($doi !== null) {
                $url = 'http://dx.doi.org/' . $doi;
            } else {
                $url = 'http://data.gulfresearchinitiative.org/data/' . $udi;
            }

            $citationString = "$author ($year) $title ($udi) " .
                "[Data files] Available from $url";

            return $citationString;
        } else {
            $citationString = "This dataset has no registration: $title ($udi)";
            return $citationString;
        }
    }
}
