<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use Pelagos\Entity\DIF;

/**
 * Dataset Entity class.
 *
 * @ORM\Entity
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
}
