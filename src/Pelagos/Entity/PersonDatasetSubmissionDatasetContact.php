<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Person to Dataset Submission dataset contact association entity class.
 *
 * @ORM\Entity
 */
class PersonDatasetSubmissionDatasetContact extends PersonDatasetSubmission
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Dataset Contact';

    /**
     * The Dataset Submission for this association.
     *
     * @var DatasetSubmission
     *
     * @ORM\ManyToOne(targetEntity="DatasetSubmission", inversedBy="datasetContacts")
     */
    protected $datasetSubmission;

    /**
     * Whether this entity is a primary contact, or not.
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $primaryContact;

    /**
     * Getter for primary contact indicator.
     *
     * @return boolean
     */
    public function isPrimaryContact()
    {
        return $this->primaryContact;
    }

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->primaryContact = false;
    }
}
