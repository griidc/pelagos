<?php

namespace App\Message;

class DeleteFile
{
    /**
     * The file ID of the file to be deleted.
     *
     * @var string
     */
    protected $fileId;

    /**
     * Constructor.
     *
     * @param int $fileId The file ID of the file to be deleted.
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
