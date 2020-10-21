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
    protected $fileIds;

    /**
     * Constructor.
     *
     * @param array $fileIds The file IDs of the to be zipped file.
     */
    public function __construct(array $fileIds)
    {
        $this->fileIds = $fileIds;
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
}
