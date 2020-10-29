<?php

namespace App\Message;

/**
 * Message for virus-scanning files.
 */
class ScanFileForVirus
{
    /**
     * The full filename to be scanned.
     *
     * @var string
     */
    protected $filePathAndName;

    /**
     * Constructor.
     *
     * @param string filePathAndName The file ID of the file to be scanned.
     */
    public function __construct(string $filePathAndName)
    {
        $this->filePathAndName = $filePathAndName;
    }

    /**
     * The file ID getter.
     *
     * @return string The full filename to be scanned.
     */
    public function getFilePathAndName(): string
    {
        return $this->filePathAndName;
    }
}
