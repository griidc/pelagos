<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Person to Dataset Submission dataset contact association entity class.
 */
#[ORM\Entity]
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
     */
    #[ORM\ManyToOne(targetEntity: 'DatasetSubmission', inversedBy: 'datasetContacts')]
    protected $datasetSubmission;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->primaryContact = false;
    }
}
