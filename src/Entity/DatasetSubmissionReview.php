<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * This class represents Pelagos DatasetSubmission Review information.
 */
#[ORM\Entity]
class DatasetSubmissionReview extends Entity
{
    use IdTrait;

    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Dataset Submission Review';

    /**
     * The Person who started this review.
     *
     * @var Person
     */
    #[ORM\ManyToOne(targetEntity: 'Person')]
    protected $reviewedBy;

    /**
     * The time and date when the review was started.
     *
     * @var \Datetime
     */
    #[ORM\Column(type: 'datetimetz', nullable: false)]
    protected $reviewStartDateTime;

    /**
     * The time and date when the review ended.
     *
     * @var \Datetime
     */
    #[ORM\Column(type: 'datetimetz', nullable: true)]
    protected $reviewEndDateTime;

    /**
     * Any notes the reviewer has provided for this review.
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $reviewNotes;

    /**
     * The Person who ended this review.
     *
     * @var Person
     */
    #[ORM\ManyToOne(targetEntity: 'Person')]
    protected $reviewEndedBy;

    /**
     * Constructor.
     *
     * Created a new datasetSubmissionReview entry.
     *
     * @param Person    $reviewedBy          The person who started this review.
     * @param \DateTime $reviewStartDateTime The DateTime the review was started.
     */
    public function __construct(
        Person $reviewedBy,
        \DateTime $reviewStartDateTime
    ) {
        $this->reviewedBy = $reviewedBy;
        $this->reviewStartDateTime = $reviewStartDateTime;
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
     * @param string|null $reviewNotes A string with the review notes.
     *
     * @return void
     */
    public function setReviewNotes(?string $reviewNotes)
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

    /**
     * Get the Person who ended the review.
     *
     * @return Person
     */
    public function getReviewEndedBy()
    {
        return $this->reviewEndedBy;
    }

    /**
     * Sets the person who has ended the review.
     *
     * @param Person $reviewEndedBy The Person who ended this review.
     *
     * @return void
     */
    public function setReviewEndedBy(Person $reviewEndedBy)
    {
        $this->reviewEndedBy = $reviewEndedBy;
    }
}
