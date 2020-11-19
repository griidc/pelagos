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
     * @return string The file path.
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }
}
