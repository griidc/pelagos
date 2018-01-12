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
     * The DatasetSubmission this Dataset Submission Review is attached to.
     *
     * @var DatasetSubmission
     *
     * @ORM\OneToOne(targetEntity="DatasetSubmission", inversedBy="datasetSubmissionReview")
     */
    protected $datasetSubmission;

    /**
     * The Person who started this review.
     *
     * @var Person
     *
     * @ORM\Column(type="text", nullable=false)
     */
    protected $reviewedBy;

    /**
     * The time and date when the review was started.
     *
     * @var \Datetime
     *
     * @ORM\Column(type="datetimetz", nullable=false)
     */
    protected $reviewStartDateTime;

    /**
     * The time and date when the review ended.
     *
     * @var \Datetime
     *
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    protected $reviewEndDateTime;

    /**
     * Any notes the reviewer has provided for this review.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $reviewNotes;

    /**
     * Constructor.
     *
     * Created a new datasetSubmissionReview entry.
     *
     * @param DatasetSubmission $datasetSubmission   The DatasetSubmission linked to this Review.
     * @param Person            $reviewedBy          The person who started this review.
     * @param \DateTime         $reviewStartDateTime The DateTime the review was started.
     */
    public function __construct(
        DatasetSubmission $datasetSubmission,
        Person $reviewedBy,
        \DateTime $reviewStartDateTime
    ) {
        $this->datasetSubmission = $datasetSubmission;
        $this->reviewedBy = $reviewedBy;
        $this->reviewStartDateTime = $reviewStartDateTime;
    }

    /**
     * Gets the Dataset Submission for this review.
     *
     * @return DatasetSubmission The DatasetSubmission for this rkeview.
     */
    public function getDatasetSubmission()
    {
        return $this->datasetSubmission;
    }

    /**
     * Get the Person who started this review.
     *
     * @return Person The Person who started this review.
     */
    public function getReviewedBy()
    {
        return $this->reviewedBy;
    }

    /**
     * Gets the date time when the review was started.
     *
     * @return \DateTime The date time when the review was started.
     */
    public function getReviewStartDateTime()
    {
        return $this->reviewStartDateTime;
    }

    /**
     * Sets the date time when the review was ended.
     *
     * @param \DateTime|null $reviewEndDateTime The date time when the review was ended.
     *
     * @return void
     */
    public function setReviewEndDateTime(\DateTime $reviewEndDateTime = null)
    {
        $this->reviewEndDateTime = $reviewEndDateTime;
    }

    /**
     * Gets the date time when the review was ended.
     *
     * @return \DateTime The date time when the review was ended..
     */
    public function getReviewEndDateTime()
    {
        return $this->reviewEndDateTime;
    }

    /**
     * Sets the text for the review notes or this review.
     *
     * @param string $reviewNotes A string with the review notes.
     *
     * @return void
     */
    public function setReviewNotes($reviewNotes)
    {
        $this->reviewNotes = $reviewNotes;
    }

    /**
     * Gets the text for the review notes or this review.
     *
     * @return string The review notes for this review.
     */
    public function getReviewNotes()
    {
        return $this->reviewNotes;
    }
}
