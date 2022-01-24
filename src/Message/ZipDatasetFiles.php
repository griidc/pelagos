<?php

namespace App\Message;

/**
 * The message for Zip file handler.
 */
class ZipDatasetFiles
{
    /**
     * The datasetSubmission Id it is associated.
     *
     * @var integer
     */
    private $datasetSubmissionId;

    /**
     * Constructor.
     *
     * @param integer $datasetSubmissionId DatasetSubmission Id it is associated with.
     */
    public function __construct(int $datasetSubmissionId)
    {
        $this->datasetSubmissionId = $datasetSubmissionId;
    }

    /**
     * Getter for datasetSubmissionId.
     *
     * @return integer
     */
    public function getDatasetSubmissionId(): int
    {
        return $this->datasetSubmissionId;
    }
}
