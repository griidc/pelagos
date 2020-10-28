<?php

namespace App\Message;

/**
 * The message for Zip file handler.
 */
class ZipFile
{
    /**
     * The file IDs of the to be zipped file.
     *
     * @var array
     */
    private $fileIds;

    /**
     * The datasetSubmission Id it is associated.
     *
     * @var integer
     */
    private $datasetSubmissionId;

    /**
     * Constructor.
     *
     * @param array   $fileIds             The file IDs of the to be zipped file.
     * @param integer $datasetSubmissionId DatasetSubmission Id it is associated with.
     */
    public function __construct(array $fileIds, int $datasetSubmissionId)
    {
        $this->fileIds = $fileIds;
        $this->datasetSubmissionId = $datasetSubmissionId;
    }

    /**
     * The file IDs getter.
     *
     * @return array
     */
    public function getFileIds(): array
    {
        return $this->fileIds;
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
