<?php

namespace App\Message;

/**
 * Message for Renaming Files.
 */
class RenameFile
{
    /**
     * The file ID of the to be renamed file.
     *
     * @var integer
     */
    protected $fileId;

    /**
     * Constructor.
     *
     * @param int $fileId The file ID of the to be renamed file.
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
