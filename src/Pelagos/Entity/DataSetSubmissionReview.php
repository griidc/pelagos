<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * This class represents Pelagos DatasetSubmission Review information.
 *
 * @ORM\Entity
 */
class DatasetSubmissionReview extends Entity
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Dataset Submission Review';
    
    /**
     * The DatasetSubmission this Dataset Submission is attached to.
     *
     * @var DatasetSubmission
     *
     * @ORM\ManyToOne(targetEntity="DatasetSubmission", inversedBy="datasetSubmissionReviewHistory", cascade={"persist"})
     *
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $datasetSubmission;
    
    /**
     * Review.
     *
     * @var integer
     *
     * @ORM\Column(type="text", nullable=false)
     */
    protected $reviewedBy;
    
    /**
     * Review.
     *
     * @var integer
     *
     * @ORM\Column(type="date", nullable=false)
     */
    protected $reviewStartDateTime;
    
    /**
     * Review.
     *
     * @var integer
     *
     * @ORM\Column(type="date", nullable=true)
     */
    protected $reviewEndDateTime;
    
    /**
     * Review.
     *
     * @var integer
     *
     * @ORM\Column(type="text", nullable=false)
     */
    protected $reviewNotes;
    
    /**
     * Constructor.
     *
     * Created a new datasetSubmissionReview entry.
     *
     * @param Entity $datasetSubmission   The DatasetSubmission linked to this Review.
     * @param Person $reviewedBy          The person who started this review.
     * @param date   $reviewStartDateTime The DateTime the review was started.
     *
     */
    public function __construct(DatasetSubmission $datasetSubmission, Person $reviewedBy, \DateTime $reviewStartDateTime)
    {
       $this->datasetSubmission = $datasetSubmission;
       $this->reviewedBy = $reviewedBy;
       $this->reviewStartDateTime = $reviewStartDateTime;
    }
    
    /**
     * Gets the text description of the spatial extent.
     *
     * @return DatasetSubmission The text description of the spatial extent.
     */
    public function getDatasetSubmission()
    {
        return $this->datasetSubmission;
    }
    
    /**
     * Gets the text description of the spatial extent.
     *
     * @return string The text description of the spatial extent.
     */
    public function getReviewedBy()
    {
        return $this->reviewedBy;
    }
    
    /**
     * The end date of the estimated data sampling/generation period.
     *
     * @return \DateTime The end date of the estimated data sampling/generation period.
     */
    public function getReviewStartDateTime()
    {
        return $this->reviewStartDateTime;
    }
    
    /**
     * Sets the end date of the estimated data sampling/generation period.
     *
     * @param \DateTime|null $reviewEndDateTime The end date of the estimated data sampling/generation period.
     *
     * @return void
     */
    public function setReviewEndDateTime(\DateTime $reviewEndDateTime = null)
    {
        $this->reviewEndDateTime = $reviewEndDateTime;
    }
    
    /**
     * The end date of the estimated data sampling/generation period.
     *
     * @return \DateTime The end date of the estimated data sampling/generation period.
     */
    public function getReviewEndDateTime()
    {
        return $this->reviewEndDateTime;
    }
    
    /**
     * Sets a text description of the spatial extent.
        *
     * @param string $reviewNotes A text description of the spatial extent.
        *
     * @return void
     */
    public function setReviewNotes($reviewNotes)
    {
        $this->reviewNotes = $reviewNotes;
    }
    
    /**
     * Gets the text description of the spatial extent.
     *
     * @return string The text description of the spatial extent.
     */
    public function getReviewNotes()
    {
        return $this->reviewNotes;
    }
}