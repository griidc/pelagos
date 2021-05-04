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
     * Dataset UDI for the file.
     *
     * @var string
     */
    private $udi;

    /**
     * Constructor.
     *
     * @param integer $fileId The fileId associated with the stream.
     * @param string  $udi    Associated dataset UDI.
     */
    public function __construct(int $fileId, string $udi)
    {
        $this->fileId = $fileId;
        $this->udi = $udi;
    }

    /**
     * The file ID getter.
     *
     * @return integer The fileId associated with the stream.
     */
    public function getFileId(): int
    {
        return $this->fileId;
    }

    /**
     * Getter for dataset UDI.
     *
     * @return string
     */
    public function getUdi(): string
    {
        return $this->udi;
    }
}
