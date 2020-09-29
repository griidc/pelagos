<?php

namespace App\Message;

/**
 * The message for dataset submission processing files.
 */
class DatasetSubmissionFiler
{
    /**
     * The dataset submission ID of the dataset.
     *
     * @var string
     */
    protected $datasetSubmissionId;

    /**
     * Constructor.
     *
     * @param int $datasetSubmissionId The dataset submission ID of the dataset.
     */
    public function __construct(int $datasetSubmissionId)
    {
        $this->datasetSubmissionId = $datasetSubmissionId;
    }

    /**
     * The dataset submission ID getter.
     *
     * @return integer The dataset submission ID.
     */
    public function getDatasetSubmissionId(): int
    {
        return $this->datasetSubmissionId;
    }
}
