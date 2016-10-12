<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Person to Dataset Submission metadata contact association entity class.
 *
 * @ORM\Entity
 */
class PersonDatasetSubmissionMetadataContact extends PersonDatasetSubmission
{
    /**
     * The Dataset Submission for this association.
     *
     * @var DatasetSubmission
     *
     * @ORM\ManyToOne(targetEntity="DatasetSubmission", inversedBy="metadataContacts")
     */
    protected $datasetSubmission;
}
