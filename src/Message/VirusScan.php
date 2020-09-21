<?php

namespace App\Message;

/**
 * Message for virus-scanning files.
 */
class VirusScan
{
    /**
     * The file ID of the file to be scanned.
     *
     * @var string
     */
    protected $fileId;

    /**
     * Constructor.
     *
     * @param int $fileId The file ID of the file to be scanned.
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
