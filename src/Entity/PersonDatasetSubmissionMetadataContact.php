<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Person to Dataset Submission metadata contact association entity class.
 */
#[ORM\Entity]
class PersonDatasetSubmissionMetadataContact extends PersonDatasetSubmission
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Metadata Contact';

    /**
     * The Dataset Submission for this association.
     *
     * @var DatasetSubmission
     */
    #[ORM\ManyToOne(targetEntity: 'DatasetSubmission', inversedBy: 'metadataContacts')]
    protected $datasetSubmission;
}
