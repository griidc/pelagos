<?php

namespace App\Message;

/**
 * The message for processing files.
 */
class ProcessFile
{
    /**
     * The file ID of the file to be hashed.
     *
     * @var integer
     */
    protected $fileId;

    /**
     * Constructor.
     *
     * @param int $fileId The file ID of the file to be processed.
     */
    public function __construct(int $fileId)
    {
        $this->fileId = $fileId;
    }


    /**
     * The file ID getter.
     *
     * @return integer The file ID.
     */
    public function getFileId(): int
    {
        return $this->fileId;
    }
}
