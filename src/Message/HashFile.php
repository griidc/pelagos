<?php

namespace App\Message;

/**
 * The message for hashing files.
 */
class HashFile
{
    /**
     * The file ID of the to be hashed file.
     *
     * @var string
     */
    protected $fileId;

    /**
     * Constructor.
     *
     * @param int $fileId The file ID of the to be hashed file.
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
