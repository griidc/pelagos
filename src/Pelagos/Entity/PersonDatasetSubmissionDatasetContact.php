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
     * The Dataset Submission for this association.
     *
     * @var DatasetSubmission
     *
     * @ORM\ManyToOne(targetEntity="DatasetSubmission", inversedBy="datasetContacts")
     */
    protected $datasetSubmission;
}
