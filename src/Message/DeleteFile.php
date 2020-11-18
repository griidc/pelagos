<?php

namespace App\Message;

class DeleteFile
{
    /**
     * The file id of the file to be deleted.
     *
     * @var integer
     */
    protected $fileId;

    /**
     * Constructor.
     *
     * @param integer $fileId The file id of the file to be deleted.
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
