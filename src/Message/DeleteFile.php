<?php

namespace App\Message;

class DeleteFile
{
    /**
     * The file path of the file to be deleted.
     *
     * @var string
     */
    protected $filePath;

    /**
     * Constructor.
     *
     * @param string $filePath The file path of the file to be deleted.
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * The file path getter.
     *
     * @return integer The file ID.
     */
    public function getFilePath(): int
    {
        return $this->filePath;
    }
}
