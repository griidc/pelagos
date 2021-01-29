<?php

namespace App\Message;

class DeleteFile
{
    /**
     * The submission to delete file from.
     *
     * @var integer
     */
    protected $datasetSubmissionId;

    /**
     * Constructor.
     *
     * @param integer $datasetSubmissionId The submission ID to delete file from.
     */
    public function __construct(int $datasetSubmissionId)
    {
        $this->datasetSubmissionId = $datasetSubmissionId;
    }

    /**
     * The Dataset Submission getter.
     *
     * @return string The file path.
     */
    public function getDatasetSubmissionId(): int
    {
        return $this->datasetSubmission;
    }
}
