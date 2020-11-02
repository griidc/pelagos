<?php

namespace App\Message;

/**
 * Message for virus-scanning files.
 */
class ScanFileForVirus
{
    /**
     * The ID of the file assocated with the stream, for identification purposes.
     *
     * @var int $fileId
     */
    private $fileId;

    /**
     * Constructor.
     *
     * @param int $fileId The fileId associated with the stream.
     */
    public function __construct(int $fileId)
    {
        $this->fileId = $fileId;
    }

    /**
     * The file ID getter.
     *
     * @return string The fileId associated with the stream.
     */
    public function getFileId(): int
    {
        return $this->fileId;
    }
}
