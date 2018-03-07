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
     * Class constructor.
     */
    public function __construct()
    {
        $this->primaryContact = false;
    }

    /**
     * Setter for primary contact indicator.
     *
     * @param boolean $state A boolean to be set, true if primary contact, false otherwise.
     *
     * @return void
     */
    public function setPrimaryContact($state)
    {
        $this->primaryContact = $state;
    }
}
